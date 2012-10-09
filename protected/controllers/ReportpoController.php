<?php

class ReportpoController extends Controller
{
	/**
	 * @var string the default layout for the views. Defaults to '//layouts/column2', meaning
	 * using two-column layout. See 'protected/views/layouts/column2.php'.
	 */
	public $layout='//layouts/column3';
protected $menuname = 'reportpo';

public function actionHelp()
	{
		if (isset($_POST['id'])) {
			$id= (int)$_POST['id'];
			switch ($id) {
				case 1 : $this->txt = '_help'; break;
				case 2 : $this->txt = '_helpmodif'; break;
				case 3 : $this->txt = '_helpdetail'; break;
				case 4 : $this->txt = '_helpdetailmodif'; break;
			}
		}
		parent::actionHelp();
	}

	/**
	 * Lists all models.
	 */
	public function actionIndex()
	{
            parent::actionIndex();
	  		$purchasingorg=new Purchasingorg('search');
	  $purchasingorg->unsetAttributes();  // clear any default values
	  if(isset($_GET['Purchasingorg']))
		$purchasingorg->attributes=$_GET['Purchasingorg'];
      $paymentmethod=new Paymentmethod('search');
	  $paymentmethod->unsetAttributes();  // clear any default values
	  if(isset($_GET['Paymentmethod']))
		$paymentmethod->attributes=$_GET['Paymentmethod'];

		$purchasinggroup=new Purchasinggroup('search');
	  $purchasinggroup->unsetAttributes();  // clear any default values
	  if(isset($_GET['Purchasinggroup']))
		$purchasinggroup->attributes=$_GET['Purchasinggroup'];

		$supplier=new Supplier('search');
	  $supplier->unsetAttributes();  // clear any default values
	  if(isset($_GET['Supplier']))
		$supplier->attributes=$_GET['Supplier'];

		$podetail=new Podetail('search');
	  $podetail->unsetAttributes();  // clear any default values
	  if(isset($_GET['Podetail']))
		$podetail->attributes=$_GET['Podetail'];

	$product=new Prmaterial('search');
	  $product->unsetAttributes();  // clear any default values
	  if(isset($_GET['Prmaterial']))
		$product->attributes=$_GET['Prmaterial'];

		$unitofmeasure=new Unitofmeasure('search');
	  $unitofmeasure->unsetAttributes();  // clear any default values
	  if(isset($_GET['Unitofmeasure']))
		$unitofmeasure->attributes=$_GET['Unitofmeasure'];

		$currency=new Currency('search');
	  $currency->unsetAttributes();  // clear any default values
	  if(isset($_GET['Currency']))
		$currency->attributes=$_GET['Currency'];

		$sloc=new Sloc('search');
	  $sloc->unsetAttributes();  // clear any default values
	  if(isset($_GET['Sloc']))
		$sloc->attributes=$_GET['Sloc'];

		$tax=new Tax('search');
	  $tax->unsetAttributes();  // clear any default values
	  if(isset($_GET['Tax']))
		$tax->attributes=$_GET['Tax'];

		$model=new Poheader('search');
		$model->unsetAttributes();  // clear any default values
		if(isset($_GET['Poheader']))
			$model->attributes=$_GET['Poheader'];
			
			if (isset($_GET['pageSize']))
		{
		  Yii::app()->user->setState('pageSize',(int)$_GET['pageSize']);
		  unset($_GET['pageSize']);  // would interfere with pager and repetitive page size change
		}

		$this->render('index',array(
			'model'=>$model,
			'purchasingorg'=>$purchasingorg,
			'purchasinggroup'=>$purchasinggroup,
            'paymentmethod'=>$paymentmethod,
			'supplier'=>$supplier,
			'podetail'=>$podetail,
			'product'=>$product,
			'unitofmeasure'=>$unitofmeasure,
			'currency'=>$currency,
			'sloc'=>$sloc,
			'tax'=>$tax,
                    'podetail'=>$podetail
		));
	}
    
     public function actionDownload()
	{
	  parent::actionDownload();
	  $sql = "select b.fullname, a.pono, a.docdate,b.addressbookid,a.poheaderid,c.paymentname
      from poheader a
      left join addressbook b on b.addressbookid = a.addressbookid
      left join paymentmethod c on c.paymentmethodid = a.paymentmethodid ";
		if ($_GET['id'] !== '') {
				$sql = $sql . "where a.poheaderid = ".$_GET['id'];
		}
		$command=$this->connection->createCommand($sql);
		$dataReader=$command->queryAll();

	  $this->pdf->title='Purchase Order';
	  $this->pdf->AddPage('P');
	  $this->pdf->setFont('Arial','B',12);

	  // definisi font
	  $this->pdf->setFont('Arial','B',8);

    foreach($dataReader as $row)
    {
      $this->pdf->setFont('Arial','B',8);
      $this->pdf->text(100,30,'Purchase Order No ');$this->pdf->text(130,30,$row['pono']);
      $this->pdf->text(100,35,'PO Date ');$this->pdf->text(130,35,date(Yii::app()->params['dateviewfromdb'], strtotime($row['docdate'])));
      $this->pdf->text(100,40,'Payment ');$this->pdf->text(130,40,$row['paymentname']);

      $sql1 = "select b.addresstypename, a.addressname, c.cityname, a.phoneno
        from address a
        left join addresstype b on b.addresstypeid = a.addresstypeid
        left join city c on c.cityid = a.cityid
        where addressbookid = ".$row['addressbookid'].
        " order by addressid ".
        " limit 1";
      $command1=$this->connection->createCommand($sql1);
      $dataReader1=$command1->queryAll();

      foreach($dataReader1 as $row1)
      {
        $this->pdf->setFont('Arial','B',6);
        $this->pdf->Rect(5,25,60,25);
        $this->pdf->text(10,30,'Vendor');
        $this->pdf->setFont('Arial','',6);
        $this->pdf->text(10,35,'Name');$this->pdf->text(20,35,': '.$row['fullname']);
        $this->pdf->text(10,40,'Address');$this->pdf->text(20,40,': '.$row1['addressname']);
        $this->pdf->text(10,45,'Phone');$this->pdf->text(20,45,': '.$row1['phoneno']);
      }

      $sql1 = "select a.poheaderid,c.uomcode,a.poqty,a.delvdate,a.netprice,(poqty * netprice) as total,b.productname,
        d.symbol,d.i18n,e.taxvalue
        from podetail a
        left join product b on b.productid = a.productid
        left join unitofmeasure c on c.unitofmeasureid = a.unitofmeasureid
        left join currency d on d.currencyid = a.currencyid
        left join tax e on e.taxid = a.taxid
        where poheaderid = ".$row['poheaderid'];
      $command1=$this->connection->createCommand($sql1);
      $dataReader1=$command1->queryAll();

      $total = 0;
      $this->pdf->sety(55);
      $this->pdf->setFont('Arial','B',6);
      $this->pdf->setaligns(array('C','C','C','C','C'));
      $this->pdf->setwidths(array(20,20,80,30,30));
      $this->pdf->setFont('Arial','',6);
      $this->pdf->Row(array('Qty','Units','Description', 'Unit Price','Total'));
      $this->pdf->setaligns(array('C','C','L','R','R'));
      foreach($dataReader1 as $row1)
      {
        Yii::app()->setLanguage($row1['i18n']);
        $this->pdf->row(array($row1['poqty'],$row1['uomcode'],$row1['productname'],
            Yii::app()->numberFormatter->formatCurrency($row1['netprice'], $row1['symbol']),
            Yii::app()->numberFormatter->formatCurrency($row1['total'], $row1['symbol'])));
        $total = $row1['total'] + $total;
      }
      Yii::app()->setLanguage('en');
      
      $this->pdf->rect(160,$this->pdf->gety(),30,5);
      $this->pdf->text(132,$this->pdf->gety()+3,'Sub Total');
      $this->pdf->text(161,$this->pdf->gety()+3,Yii::app()->numberFormatter->formatCurrency($total, $row1['symbol']));
      $this->pdf->sety($this->pdf->gety()+5);
      $this->pdf->rect(160,$this->pdf->gety(),30,5);
      $this->pdf->text(132,$this->pdf->gety()+3,'Total');
      $this->pdf->text(161,$this->pdf->gety()+3,Yii::app()->numberFormatter->formatCurrency($total, $row1['symbol']));
    }
	  $this->pdf->Output();
	}


	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer the ID of the model to be loaded
	 */
	public function loadModel($id)
	{
		$model=Reportpo::model()->findByPk((int)$id);
		if($model===null)
			throw new CHttpException(404,'The requested page does not exist.');
		return $model;
	}
	/**
	 * Performs the AJAX validation.
	 * @param CModel the model to be validated
	 */
	protected function performAjaxValidation($model)
	{
		if(isset($_POST['ajax']) && $_POST['ajax']==='poheader-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}
}
