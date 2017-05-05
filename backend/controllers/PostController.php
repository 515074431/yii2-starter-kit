<?php

namespace backend\controllers;

use Yii;
use common\models\Post;
use backend\models\search\PostSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * PostController implements the CRUD actions for Post model.
 */
class PostController extends Controller
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
        ];
    }

    /**
     * Lists all Post models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new PostSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Post model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Post model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new Post();

        //if ($model->load(Yii::$app->request->post()) && $model->save()) {
        if ($model->load(Yii::$app->request->post())) {
            $model->status = 'draft';
            if($model->save()){
                echo 'the status is : ' . $model->status;
            }else{
                var_dump($model->errors);
            }
            //return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing Post model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing Post model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    public function actionTest(){
        $post2 = Post::findOne(1);


        // the safe way
        echo '当前状态:';
        echo '<br>status id    = ' . $post2->getWorkflowStatus()->getId();
        echo '<br>status label = ' . $post2->getWorkflowStatus()->getLabel();
        echo '<br>status color = ' . $post2->getWorkflowStatus()->getMetadata('color');

        // ask the WorkflowSource
        $transitions = $post2
            ->getWorkflowSource()
            ->getTransitions($post2->getWorkflowStatus()->getId());
        echo '<pre>';
        foreach( $transitions as $transition ) {
            echo '<br>===========================================================<br>';
            //var_dump($transition);

            //echo $transition->getEndStatus()->getId() .'<br>';
            echo '<br>下个状态：';
            // the safe way
            echo '<br>status id    = ' . $transition->getEndStatus()->getId();
            echo '<br>status label = ' . $transition->getEndStatus()->getLabel();
            echo '<br>status color = ' . $transition->getEndStatus()->getMetadata('color');

        }
        //$post2->sendToStatus('ready');
        //$post2->save();
        exit;

        //var_dump($post2);exit;
        echo '(1) the status is : ' . $post2->getWorkflowStatus()->getId();
        echo '<br>(2) the status is : ' . $post2->status;
        if( $post2->getWorkflowStatus()->getId() == 'post/draft'){
            $post2->sendToStatus('correction');
        }
        if( $post2->getWorkflowStatus()->getId() == 'post/correction'){
            $post2->sendToStatus('ready');
        }
        if( $post2->getWorkflowStatus()->getId() == 'post/ready'){
            $post2->sendToStatus('published');
        }
        $post2->save();
        echo '修改状态';
    }

    /**
     * Finds the Post model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Post the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Post::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}
