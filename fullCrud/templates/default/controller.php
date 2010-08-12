<?php echo "<?php\n"; ?>

class <?php echo $this->controllerClass; ?> extends <?php echo $this->baseControllerClass."\n"; ?>
{
	public $layout='//layouts/column2';
	private $_model;

	<?php Yii::app()->controller->renderPartial('auth'.DIRECTORY_SEPARATOR.$this->authtype); ?>

	public function actionView()
	{
		$this->render('view',array(
			'model' => $this->loadModel(),
		));
	}

	
		<?php if($this->persistent_sessions) { ?>
	public function unpickleForm(&$model) {
		if(isset($_SESSION['<?php echo $this->modelClass; ?>'])) 
			$model->attributes = $_SESSION['<?php echo $this->modelClass; ?>'];
  }

	public function pickleForm(&$model, $formdata) {
		foreach($formdata as $key => $value) 
			if(is_array($value))
				$_SESSION[$key] = $value;
	}

    <?php } ?>
	public function actionCreate()
	{
		$model = new <?php echo $this->modelClass; ?>;

		<?php if($this->persistent_sessions) { ?>
			$this->pickleForm($model, $_POST);
		<?php } ?>

		<?php if($this->enable_ajax_validation) { ?>
		$this->performAjaxValidation($model);
    <?php } ?>

		if(isset($_POST['<?php echo $this->modelClass; ?>']))
		{
			$model->attributes = $_POST['<?php echo $this->modelClass; ?>'];

<?php
			// Add additional MANY_MANY Attributes to the model object
			foreach(CActiveRecord::model($this->model)->relations() as $key => $relation)
			{
				if($relation[0] == 'CManyManyRelation')
				{
					printf("\t\t\tif(isset(\$_POST['%s']['%s']))\n", $this->modelClass, $relation[1]);
					printf("\t\t\t\t\$model->setRelationRecords('%s', \$_POST['%s']['%s']);\n", $key, $this->modelClass, $relation[1]);
				}
			}
?>

			if($model->save()) {
		<?php if($this->persistent_sessions) { ?>
				unset($_SESSION['<?php echo $this->modelClass; ?>']);
    <?php } ?>

				if(Yii::app()->request->isAjaxRequest)
					echo 'Data has been saved. ' . $this->closeButton();
				else if(isset($_POST['returnUrl'])) 
					$this->redirect($_POST['returnUrl']); 
				else
					$this->redirect(array('view','id'=>$model-><?php echo $this->tableSchema->primaryKey; ?>));
				}
			}

		if(isset($_POST['returnUrl']))
			$returnUrl = $_POST['returnUrl'];
		else
			$returnUrl = array('<?php echo strtolower($this->modelClass) ?>/admin');

		if(Yii::app()->request->isPostRequest) {
			$this->renderPartial('_miniform',array(
						'model'=>$model,
						));
		} else {
			$this->render('create',array(
						'model'=>$model,
						'returnUrl' => $returnUrl
						));
		}
	}

		public function closeButton() 
		{
			return CHtml::Button('Close', array(
						'onClick' => "$('#<?php echo strtolower($this->modelClass); ?>').hide();"), array(
						'id' => '<?php echo $this->modelClass;?>CloseButton'));
		}

	public function actionUpdate()
	{
		$model = $this->loadModel();

		<?php if($this->persistent_sessions) { ?>
    $this->pickleForm($model, $_POST);
		<?php } ?>

		<?php if($this->enable_ajax_validation) { ?>
    $this->performAjaxValidation($model);
		<?php } ?>

		if(isset($_POST['<?php echo $this->modelClass; ?>']))
		{
			$model->attributes = $_POST['<?php echo $this->modelClass; ?>'];

<?php
			foreach(CActiveRecord::model($this->model)->relations() as $key => $relation)
			{
				if($relation[0] == 'CManyManyRelation')
				{
					printf("\t\t\tif(isset(\$_POST['%s']['%s']))\n", $this->model, $relation[1]);
					printf("\t\t\t\t\$model->setRelationRecords('%s', \$_POST['%s']['%s']);\n", $key, $this->modelClass, $relation[1]);
				}
			}
?>

			if($model->save()) {
		<?php if($this->persistent_sessions) { ?>
      unset($_SESSION['<?php echo $this->modelClass; ?>']);
		<?php } ?>

      $this->redirect(array('view','id'=>$model-><?php echo $this->tableSchema->primaryKey; ?>));
			}
		}

		if(isset($_POST['returnUrl']))
			$returnUrl = $_POST['returnUrl'];
		else
			$returnUrl = array('<?php echo strtolower($this->modelClass) ?>/admin');

		$this->render('update',array(
			'model'=>$model,
			'returnUrl' => $returnUrl
		));
	}

	public function actionDelete()
	{
		if(Yii::app()->request->isPostRequest)
		{
			$this->loadModel()->delete();

			if(!isset($_GET['ajax']))
			{
				if(isset($_POST['returnUrl']))
					$this->redirect($_POST['returnUrl']); 
				else
					$this->redirect(array('admin'));
			}
		}
		else
			throw new CHttpException(400,
					Yii::t('app', 'Invalid request. Please do not repeat this request again.'));
	}

	public function actionIndex()
	{
		$dataProvider=new CActiveDataProvider('<?php echo $this->modelClass; ?>');
		$this->render('index',array(
			'dataProvider'=>$dataProvider,
		));
	}

	public function actionAdmin()
	{
		$model=new <?php echo $this->modelClass; ?>('search');
		$model->unsetAttributes();

		if(isset($_GET['<?php echo $this->modelClass; ?>']))
			$model->attributes = $_GET['<?php echo $this->modelClass; ?>'];

		$this->render('admin',array(
			'model'=>$model,
		));
	}

	public function loadModel()
	{
		if($this->_model === null)
		{
			if(isset($_GET['id']))
				$this->_model = CActiveRecord::model('<?php echo $this->modelClass; ?>')->findbyPk($_GET['id']);

			if($this->_model===null)
				throw new CHttpException(404, Yii::t('app', 'The requested page does not exist.'));
		}
		return $this->_model;
	}

		<?php if($this->enable_ajax_validation) { ?>
	protected function performAjaxValidation($model)
	{
		if(isset($_POST['ajax']) && $_POST['ajax'] == '<?php echo $this->class2id($this->modelClass); ?>-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}
  <?php } ?>
}
