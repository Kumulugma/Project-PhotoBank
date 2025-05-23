<?php

namespace backend\controllers;

use Yii;
use common\models\User;
use common\models\search\UserSearch;
use common\models\AuditLog;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;

/**
 * UsersController implements the CRUD actions for User model with audit logging.
 */
class UsersController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['admin'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                    'activate' => ['POST'],
                    'deactivate' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all User models.
     * @return mixed
     */
    public function actionIndex()
    {
        AuditLog::logSystemEvent('Przeglądanie listy użytkowników', AuditLog::SEVERITY_INFO, AuditLog::ACTION_ACCESS);
        
        $searchModel = new UserSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single User model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        
        AuditLog::logSystemEvent("Podgląd profilu użytkownika: {$model->username}", 
            AuditLog::SEVERITY_INFO, AuditLog::ACTION_ACCESS, [
                'model_class' => get_class($model),
                'model_id' => $model->id
            ]);
        
        // Get user roles
        $auth = Yii::$app->authManager;
        $roles = $auth->getRolesByUser($id);
        
        // Get user statistics
        $stats = [
            'photos_uploaded' => \common\models\Photo::find()->where(['created_by' => $id])->count(),
            'photos_active' => \common\models\Photo::find()
                ->where(['created_by' => $id, 'status' => \common\models\Photo::STATUS_ACTIVE])
                ->count(),
            'last_login' => $model->last_login_at ? date('d.m.Y H:i', $model->last_login_at) : 'Nigdy',
            'account_age_days' => $model->created_at ? floor((time() - $model->created_at) / 86400) : 0,
        ];
        
        // Get recent activity from audit log
        $recentActivity = AuditLog::find()
            ->where(['user_id' => $id])
            ->orderBy(['created_at' => SORT_DESC])
            ->limit(10)
            ->all();
        
        return $this->render('view', [
            'model' => $model,
            'roles' => array_keys($roles),
            'stats' => $stats,
            'recentActivity' => $recentActivity,
        ]);
    }

    /**
     * Creates a new User model.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new User();
        $model->scenario = 'create';

        if ($model->load(Yii::$app->request->post())) {
            $model->setPassword($model->password);
            $model->generateAuthKey();
            $model->status = User::STATUS_ACTIVE;
            $model->created_at = time();
            $model->updated_at = time();
            
            $requestedRole = Yii::$app->request->post('role', 'user');
            
            if ($model->save()) {
                // Assign role
                $auth = Yii::$app->authManager;
                $role = $auth->getRole($requestedRole);
                
                if ($role) {
                    $auth->assign($role, $model->id);
                    
                    AuditLog::logModelAction($model, AuditLog::ACTION_CREATE);
                    AuditLog::logSystemEvent("Utworzono nowego użytkownika: {$model->username} z rolą: {$requestedRole}", 
                        AuditLog::SEVERITY_SUCCESS, AuditLog::ACTION_CREATE, [
                            'model_class' => get_class($model),
                            'model_id' => $model->id,
                            'new_values' => [
                                'username' => $model->username,
                                'email' => $model->email,
                                'role' => $requestedRole,
                                'status' => $model->status
                            ]
                        ]);
                } else {
                    AuditLog::logSystemEvent("Błąd przypisywania roli {$requestedRole} do użytkownika {$model->username}", 
                        AuditLog::SEVERITY_WARNING, AuditLog::ACTION_CREATE);
                }
                
                Yii::$app->session->setFlash('success', 'Użytkownik został pomyślnie utworzony.');
                return $this->redirect(['view', 'id' => $model->id]);
            } else {
                AuditLog::logSystemEvent('Błąd tworzenia użytkownika: ' . json_encode($model->errors), 
                    AuditLog::SEVERITY_ERROR, AuditLog::ACTION_CREATE);
            }
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing User model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $model->scenario = 'update';
        $oldAttributes = $model->attributes;
        
        // Get current role
        $auth = Yii::$app->authManager;
        $roles = $auth->getRolesByUser($id);
        $currentRole = key($roles);

        if ($model->load(Yii::$app->request->post())) {
            // Only set password if provided
            if (!empty($model->password)) {
                $model->setPassword($model->password);
                $model->generateAuthKey();
            }
            
            $model->updated_at = time();
            
            // Update role if changed
            $newRole = Yii::$app->request->post('role');
            $roleChanged = $newRole && $newRole !== $currentRole;
            
            if ($model->save()) {
                // Log user update
                AuditLog::logModelAction($model, AuditLog::ACTION_UPDATE, $oldAttributes);
                
                // Update role if needed
                if ($roleChanged) {
                    // Revoke all current roles
                    $auth->revokeAll($id);
                    
                    // Assign new role
                    $role = $auth->getRole($newRole);
                    if ($role) {
                        $auth->assign($role, $model->id);
                        
                        AuditLog::logSystemEvent("Zmieniono rolę użytkownika {$model->username}: {$currentRole} → {$newRole}", 
                            AuditLog::SEVERITY_INFO, AuditLog::ACTION_UPDATE, [
                                'model_class' => get_class($model),
                                'model_id' => $model->id,
                                'old_values' => ['role' => $currentRole],
                                'new_values' => ['role' => $newRole]
                            ]);
                    } else {
                        AuditLog::logSystemEvent("Błąd przypisywania nowej roli {$newRole} do użytkownika {$model->username}", 
                            AuditLog::SEVERITY_ERROR, AuditLog::ACTION_UPDATE);
                    }
                }
                
                // Check for significant changes
                $significantChanges = [];
                if ($oldAttributes['username'] !== $model->username) {
                    $significantChanges[] = "nazwa użytkownika: {$oldAttributes['username']} → {$model->username}";
                }
                if ($oldAttributes['email'] !== $model->email) {
                    $significantChanges[] = "email: {$oldAttributes['email']} → {$model->email}";
                }
                if ($oldAttributes['status'] !== $model->status) {
                    $statusNames = [User::STATUS_ACTIVE => 'aktywny', User::STATUS_INACTIVE => 'nieaktywny', User::STATUS_DELETED => 'usunięty'];
                    $oldStatusName = $statusNames[$oldAttributes['status']] ?? $oldAttributes['status'];
                    $newStatusName = $statusNames[$model->status] ?? $model->status;
                    $significantChanges[] = "status: {$oldStatusName} → {$newStatusName}";
                }
                
                if (!empty($significantChanges)) {
                    AuditLog::logSystemEvent("Zaktualizowano użytkownika {$model->username}: " . implode(', ', $significantChanges), 
                        AuditLog::SEVERITY_SUCCESS, AuditLog::ACTION_UPDATE, [
                            'model_class' => get_class($model),
                            'model_id' => $model->id
                        ]);
                }
                
                Yii::$app->session->setFlash('success', 'Użytkownik został pomyślnie zaktualizowany.');
                return $this->redirect(['view', 'id' => $model->id]);
            } else {
                AuditLog::logSystemEvent("Błąd aktualizacji użytkownika ID {$id}: " . json_encode($model->errors), 
                    AuditLog::SEVERITY_ERROR, AuditLog::ACTION_UPDATE);
            }
        }

        return $this->render('update', [
            'model' => $model,
            'currentRole' => $currentRole,
        ]);
    }

    /**
     * Activates a user account.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionActivate($id)
    {
        $model = $this->findModel($id);
        
        if ($model->status === User::STATUS_ACTIVE) {
            Yii::$app->session->setFlash('info', 'Użytkownik jest już aktywny.');
            return $this->redirect(['view', 'id' => $id]);
        }
        
        $oldStatus = $model->status;
        $model->status = User::STATUS_ACTIVE;
        $model->updated_at = time();
        
        if ($model->save(false)) {
            AuditLog::logSystemEvent("Aktywowano konto użytkownika: {$model->username}", 
                AuditLog::SEVERITY_SUCCESS, AuditLog::ACTION_UPDATE, [
                    'model_class' => get_class($model),
                    'model_id' => $model->id,
                    'old_values' => ['status' => $oldStatus],
                    'new_values' => ['status' => $model->status]
                ]);
            
            Yii::$app->session->setFlash('success', 'Konto użytkownika zostało aktywowane.');
        } else {
            AuditLog::logSystemEvent("Błąd aktywacji konta użytkownika ID {$id}", 
                AuditLog::SEVERITY_ERROR, AuditLog::ACTION_UPDATE);
            
            Yii::$app->session->setFlash('error', 'Wystąpił błąd podczas aktywacji konta.');
        }
        
        return $this->redirect(['view', 'id' => $id]);
    }

    /**
     * Deactivates a user account.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDeactivate($id)
    {
        $model = $this->findModel($id);
        
        // Check if trying to deactivate the currently logged in user
        if ($model->id === Yii::$app->user->id) {
            AuditLog::logSystemEvent("Próba dezaktywacji własnego konta przez użytkownika: {$model->username}", 
                AuditLog::SEVERITY_WARNING, AuditLog::ACTION_UPDATE);
            
            Yii::$app->session->setFlash('error', 'Nie możesz dezaktywować własnego konta.');
            return $this->redirect(['view', 'id' => $id]);
        }
        
        if ($model->status === User::STATUS_INACTIVE) {
            Yii::$app->session->setFlash('info', 'Użytkownik jest już nieaktywny.');
            return $this->redirect(['view', 'id' => $id]);
        }
        
        $oldStatus = $model->status;
        $model->status = User::STATUS_INACTIVE;
        $model->updated_at = time();
        
        if ($model->save(false)) {
            AuditLog::logSystemEvent("Dezaktywowano konto użytkownika: {$model->username}", 
                AuditLog::SEVERITY_WARNING, AuditLog::ACTION_UPDATE, [
                    'model_class' => get_class($model),
                    'model_id' => $model->id,
                    'old_values' => ['status' => $oldStatus],
                    'new_values' => ['status' => $model->status]
                ]);
            
            Yii::$app->session->setFlash('success', 'Konto użytkownika zostało dezaktywowane.');
        } else {
            AuditLog::logSystemEvent("Błąd dezaktywacji konta użytkownika ID {$id}", 
                AuditLog::SEVERITY_ERROR, AuditLog::ACTION_UPDATE);
            
            Yii::$app->session->setFlash('error', 'Wystąpił błąd podczas dezaktywacji konta.');
        }
        
        return $this->redirect(['view', 'id' => $id]);
    }

    /**
     * Deletes an existing User model (soft delete).
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $username = $model->username;
        
        // Check if attempting to delete the only admin
        $auth = Yii::$app->authManager;
        $adminRole = $auth->getRole('admin');
        $adminUsers = $auth->getUserIdsByRole('admin');
        
        if (count($adminUsers) <= 1 && in_array($id, $adminUsers)) {
            AuditLog::logSystemEvent("Próba usunięcia jedynego administratora: {$username}", 
                AuditLog::SEVERITY_WARNING, AuditLog::ACTION_DELETE);
            
            Yii::$app->session->setFlash('error', 'Nie można usunąć jedynego administratora.');
            return $this->redirect(['index']);
        }
        
        // Check if trying to delete the currently logged in user
        if ($model->id === Yii::$app->user->id) {
            AuditLog::logSystemEvent("Próba usunięcia własnego konta przez użytkownika: {$username}", 
                AuditLog::SEVERITY_WARNING, AuditLog::ACTION_DELETE);
            
            Yii::$app->session->setFlash('error', 'Nie możesz usunąć własnego konta.');
            return $this->redirect(['index']);
        }
        
        $transaction = Yii::$app->db->beginTransaction();
        
        try {
            // Get user statistics before deletion
            $userStats = [
                'photos_count' => \common\models\Photo::find()->where(['created_by' => $id])->count(),
                'roles' => array_keys($auth->getRolesByUser($id)),
                'last_login' => $model->last_login_at,
                'account_age_days' => $model->created_at ? floor((time() - $model->created_at) / 86400) : 0,
            ];
            
            // Set status to deleted (soft delete)
            $model->status = User::STATUS_DELETED;
            $model->updated_at = time();
            
            if (!$model->save(false)) {
                throw new \Exception('Nie udało się oznaczyć użytkownika jako usuniętego');
            }
            
            // Revoke all roles
            $auth->revokeAll($id);
            
            // Log deletion with statistics
            AuditLog::logModelAction($model, AuditLog::ACTION_DELETE);
            AuditLog::logSystemEvent("Usunięto użytkownika: {$username} (zdjęć: {$userStats['photos_count']}, role: " . implode(',', $userStats['roles']) . ", wiek konta: {$userStats['account_age_days']} dni)", 
                AuditLog::SEVERITY_SUCCESS, AuditLog::ACTION_DELETE, [
                    'model_class' => get_class($model),
                    'model_id' => $model->id,
                    'old_values' => array_merge($model->attributes, $userStats)
                ]);
            
            $transaction->commit();
            
            Yii::$app->session->setFlash('success', 'Użytkownik został pomyślnie usunięty.');
            
        } catch (\Exception $e) {
            $transaction->rollBack();
            AuditLog::logSystemEvent("Błąd usuwania użytkownika ID {$id}: " . $e->getMessage(), 
                AuditLog::SEVERITY_ERROR, AuditLog::ACTION_DELETE);
            
            Yii::$app->session->setFlash('error', 'Wystąpił błąd podczas usuwania użytkownika: ' . $e->getMessage());
        }

        return $this->redirect(['index']);
    }

    /**
     * Reset user password
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionResetPassword($id)
    {
        $model = $this->findModel($id);
        
        if (Yii::$app->request->isPost) {
            $newPassword = Yii::$app->request->post('new_password');
            
            if (empty($newPassword) || strlen($newPassword) < 8) {
                Yii::$app->session->setFlash('error', 'Hasło musi mieć co najmniej 8 znaków.');
                return $this->redirect(['view', 'id' => $id]);
            }
            
            try {
                $model->setPassword($newPassword);
                $model->generateAuthKey();
                $model->updated_at = time();
                
                if ($model->save(false)) {
                    AuditLog::logSystemEvent("Zresetowano hasło użytkownika: {$model->username}", 
                        AuditLog::SEVERITY_INFO, AuditLog::ACTION_UPDATE, [
                            'model_class' => get_class($model),
                            'model_id' => $model->id
                        ]);
                    
                    Yii::$app->session->setFlash('success', 'Hasło zostało pomyślnie zresetowane.');
                } else {
                    throw new \Exception('Nie udało się zapisać nowego hasła');
                }
                
            } catch (\Exception $e) {
                AuditLog::logSystemEvent("Błąd resetowania hasła użytkownika ID {$id}: " . $e->getMessage(), 
                    AuditLog::SEVERITY_ERROR, AuditLog::ACTION_UPDATE);
                
                Yii::$app->session->setFlash('error', 'Wystąpił błąd podczas resetowania hasła: ' . $e->getMessage());
            }
        }
        
        return $this->redirect(['view', 'id' => $id]);
    }

    /**
     * Impersonate user (login as another user)
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionImpersonate($id)
    {
        $targetUser = $this->findModel($id);
        $currentUser = Yii::$app->user->identity;
        
        if ($targetUser->status !== User::STATUS_ACTIVE) {
            AuditLog::logSystemEvent("Próba podszywania się pod nieaktywnego użytkownika: {$targetUser->username}", 
                AuditLog::SEVERITY_WARNING, AuditLog::ACTION_ACCESS);
            
            Yii::$app->session->setFlash('error', 'Nie można zalogować się jako nieaktywny użytkownik.');
            return $this->redirect(['view', 'id' => $id]);
        }
        
        if ($targetUser->id === $currentUser->id) {
            Yii::$app->session->setFlash('info', 'Jesteś już zalogowany jako ten użytkownik.');
            return $this->redirect(['view', 'id' => $id]);
        }
        
        // Store original user ID in session for later restoration
        Yii::$app->session->set('impersonating_from', $currentUser->id);
        
        // Login as target user
        Yii::$app->user->logout();
        Yii::$app->user->login($targetUser);
        
        AuditLog::logSystemEvent("Administrator {$currentUser->username} zalogował się jako {$targetUser->username}", 
            AuditLog::SEVERITY_WARNING, AuditLog::ACTION_LOGIN, [
                'model_class' => get_class($targetUser),
                'model_id' => $targetUser->id,
                'old_values' => ['original_user' => $currentUser->username],
                'new_values' => ['impersonated_user' => $targetUser->username]
            ]);
        
        Yii::$app->session->setFlash('info', "Jesteś teraz zalogowany jako {$targetUser->username}. Używaj tej funkcji ostrożnie!");
        
        return $this->redirect(['/site/index']);
    }

    /**
     * Stop impersonating and return to original user
     * @return mixed
     */
    public function actionStopImpersonating()
    {
        $originalUserId = Yii::$app->session->get('impersonating_from');
        $currentUser = Yii::$app->user->identity;
        
        if (!$originalUserId) {
            Yii::$app->session->setFlash('error', 'Nie podszywasz się pod żadnego użytkownika.');
            return $this->redirect(['/site/index']);
        }
        
        $originalUser = User::findOne($originalUserId);
        if (!$originalUser || $originalUser->status !== User::STATUS_ACTIVE) {
            AuditLog::logSystemEvent("Błąd powrotu z podszywania - oryginalny użytkownik niedostępny (ID: {$originalUserId})", 
                AuditLog::SEVERITY_ERROR, AuditLog::ACTION_LOGIN);
            
            Yii::$app->session->setFlash('error', 'Nie można wrócić do oryginalnego konta.');
            return $this->redirect(['/site/logout']);
        }
        
        // Return to original user
        Yii::$app->user->logout();
        Yii::$app->user->login($originalUser);
        Yii::$app->session->remove('impersonating_from');
        
        AuditLog::logSystemEvent("Zakończono podszywanie się - powrót do konta: {$originalUser->username} (był zalogowany jako: {$currentUser->username})", 
            AuditLog::SEVERITY_INFO, AuditLog::ACTION_LOGIN, [
                'model_class' => get_class($originalUser),
                'model_id' => $originalUser->id,
                'old_values' => ['impersonated_user' => $currentUser->username],
                'new_values' => ['restored_user' => $originalUser->username]
            ]);
        
        Yii::$app->session->setFlash('success', 'Powrócono do oryginalnego konta.');
        
        return $this->redirect(['users/index']);
    }

    /**
     * Export users list
     * @return mixed
     */
    public function actionExport()
    {
        $format = Yii::$app->request->get('format', 'csv');
        
        AuditLog::logSystemEvent("Eksport listy użytkowników - format: {$format}", 
            AuditLog::SEVERITY_INFO, AuditLog::ACTION_EXPORT);
        
        $users = User::find()
            ->where(['!=', 'status', User::STATUS_DELETED])
            ->orderBy(['username' => SORT_ASC])
            ->all();
        
        if ($format === 'json') {
            $data = [];
            foreach ($users as $user) {
                $auth = Yii::$app->authManager;
                $roles = array_keys($auth->getRolesByUser($user->id));
                
                $data[] = [
                    'id' => $user->id,
                    'username' => $user->username,
                    'email' => $user->email,
                    'status' => $user->status,
                    'roles' => $roles,
                    'created_at' => date('Y-m-d H:i:s', $user->created_at),
                    'last_login_at' => $user->last_login_at ? date('Y-m-d H:i:s', $user->last_login_at) : null,
                ];
            }
            
            $filename = 'users_' . date('Y-m-d_H-i-s') . '.json';
            
            Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
            Yii::$app->response->setDownloadHeaders($filename, 'application/json');
            
            return $data;
        } else {
            // CSV format
            $filename = 'users_' . date('Y-m-d_H-i-s') . '.csv';
            
            Yii::$app->response->format = \yii\web\Response::FORMAT_RAW;
            Yii::$app->response->setDownloadHeaders($filename, 'text/csv');
            
            $output = fopen('php://output', 'w');
            
            // UTF-8 BOM
            fwrite($output, "\xEF\xBB\xBF");
            
            // Headers
            fputcsv($output, ['ID', 'Nazwa użytkownika', 'Email', 'Status', 'Role', 'Data utworzenia', 'Ostatnie logowanie'], ';');
            
            foreach ($users as $user) {
                $auth = Yii::$app->authManager;
                $roles = array_keys($auth->getRolesByUser($user->id));
                
                $statusNames = [
                    User::STATUS_ACTIVE => 'Aktywny',
                    User::STATUS_INACTIVE => 'Nieaktywny',
                    User::STATUS_DELETED => 'Usunięty'
                ];
                
                fputcsv($output, [
                    $user->id,
                    $user->username,
                    $user->email,
                    $statusNames[$user->status] ?? $user->status,
                    implode(', ', $roles),
                    date('Y-m-d H:i:s', $user->created_at),
                    $user->last_login_at ? date('Y-m-d H:i:s', $user->last_login_at) : 'Nigdy'
                ], ';');
            }
            
            fclose($output);
            return Yii::$app->response;
        }
    }

    /**
     * Bulk operations on users
     * @return mixed
     */
    public function actionBulkAction()
    {
        $action = Yii::$app->request->post('bulk_action');
        $userIds = Yii::$app->request->post('user_ids', []);
        
        if (empty($action) || empty($userIds)) {
            Yii::$app->session->setFlash('error', 'Nie wybrano akcji lub użytkowników.');
            return $this->redirect(['index']);
        }
        
        AuditLog::logSystemEvent("Rozpoczęto operację grupową: {$action} na " . count($userIds) . " użytkownikach", 
            AuditLog::SEVERITY_INFO, AuditLog::ACTION_UPDATE);
        
        $successCount = 0;
        $errorCount = 0;
        $transaction = Yii::$app->db->beginTransaction();
        
        try {
            foreach ($userIds as $userId) {
                $user = User::findOne($userId);
                if (!$user) {
                    $errorCount++;
                    continue;
                }
                
                switch ($action) {
                    case 'activate':
                        if ($user->status !== User::STATUS_ACTIVE) {
                            $user->status = User::STATUS_ACTIVE;
                            $user->updated_at = time();
                            if ($user->save(false)) {
                                $successCount++;
                            } else {
                                $errorCount++;
                            }
                        }
                        break;
                        
                    case 'deactivate':
                        if ($user->status === User::STATUS_ACTIVE && $user->id !== Yii::$app->user->id) {
                            $user->status = User::STATUS_INACTIVE;
                            $user->updated_at = time();
                            if ($user->save(false)) {
                                $successCount++;
                            } else {
                                $errorCount++;
                            }
                        }
                        break;
                        
                    case 'delete':
                        if ($user->id !== Yii::$app->user->id) {
                            $user->status = User::STATUS_DELETED;
                            $user->updated_at = time();
                            if ($user->save(false)) {
                                // Revoke all roles
                                Yii::$app->authManager->revokeAll($userId);
                                $successCount++;
                            } else {
                                $errorCount++;
                            }
                        }
                        break;
                }
            }
            
            $transaction->commit();
            
            AuditLog::logSystemEvent("Zakończono operację grupową {$action} - sukces: {$successCount}, błędy: {$errorCount}", 
                $errorCount > 0 ? AuditLog::SEVERITY_WARNING : AuditLog::SEVERITY_SUCCESS, 
                AuditLog::ACTION_UPDATE);
            
            if ($successCount > 0) {
                Yii::$app->session->setFlash('success', "Pomyślnie wykonano operację na {$successCount} użytkownikach.");
            }
            
            if ($errorCount > 0) {
                Yii::$app->session->setFlash('warning', "Wystąpiły błędy podczas operacji na {$errorCount} użytkownikach.");
            }
            
        } catch (\Exception $e) {
            $transaction->rollBack();
            AuditLog::logSystemEvent("Błąd operacji grupowej {$action}: " . $e->getMessage(), 
                AuditLog::SEVERITY_ERROR, AuditLog::ACTION_UPDATE);
            
            Yii::$app->session->setFlash('error', 'Wystąpił błąd podczas wykonywania operacji: ' . $e->getMessage());
        }
        
        return $this->redirect(['index']);
    }

    /**
     * Finds the User model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return User the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = User::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('Żądany użytkownik nie istnieje.');
    }
}