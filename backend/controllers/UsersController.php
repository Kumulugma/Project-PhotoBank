<?php

namespace backend\controllers;

use Yii;
use common\models\User;
use common\models\UserSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;

/**
 * UsersController implements the CRUD actions for User model.
 */
class UsersController extends Controller
{
    /**
     * {@inheritdoc}
     */
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
        
        // Get user roles
        $auth = Yii::$app->authManager;
        $roles = $auth->getRolesByUser($id);
        
        return $this->render('view', [
            'model' => $model,
            'roles' => array_keys($roles),
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
            
            if ($model->save()) {
                // Assign role
                $auth = Yii::$app->authManager;
                $role = $auth->getRole(Yii::$app->request->post('role', 'user'));
                $auth->assign($role, $model->id);
                
                Yii::$app->session->setFlash('success', 'User created successfully.');
                return $this->redirect(['view', 'id' => $model->id]);
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
                // Update role if needed
                if ($roleChanged) {
                    // Revoke all current roles
                    $auth->revokeAll($id);
                    
                    // Assign new role
                    $role = $auth->getRole($newRole);
                    $auth->assign($role, $model->id);
                }
                
                Yii::$app->session->setFlash('success', 'User updated successfully.');
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        return $this->render('update', [
            'model' => $model,
            'currentRole' => $currentRole,
        ]);
    }

    /**
     * Deletes an existing User model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        
        // Check if attempting to delete the only admin
        $auth = Yii::$app->authManager;
        $adminRole = $auth->getRole('admin');
        $adminUsers = $auth->getUserIdsByRole('admin');
        
        if (count($adminUsers) <= 1 && in_array($id, $adminUsers)) {
            Yii::$app->session->setFlash('error', 'Cannot delete the only administrator.');
            return $this->redirect(['index']);
        }
        
        // Set status to deleted (soft delete)
        $model->status = User::STATUS_DELETED;
        $model->save(false);
        
        // Revoke all roles
        $auth->revokeAll($id);
        
        Yii::$app->session->setFlash('success', 'User deleted successfully.');

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

        throw new NotFoundHttpException('The requested user does not exist.');
    }
}