<?php
/**
 * TipManager is a namespace that should be added to your administration interface
 * if you are using DB aware tips.
 * 
 * Once added it will appear in the application menu and will not require any
 * additional code
 * 
 * Use descendants if you want to change its looks
 * 
 * Created on 08.09.2006 by *Camper* (camper@adevel.com)
 */
class TipManager extends Namespace{
	public $tip_types=array('regular'=>'regular','trigger'=>'trigger','announce'=>'announce');
	
	function initNamespace(){
		//use tips to show help on how to use them
		$this->api->getElement('menu')->addMenuItem('Tip manager',$this->short_name.';Index');
	}
    function initLayout(){
        parent::initLayout();
        $this->template->del('LeftSidebar');
        $this->template->del('RightSidebar');
        $this->template->del('InfoWindow');
        $this->template->del('Locator');
        //$this->template->del('SelfTips');
        
		$this->tips=$this->getTipsFromTemplate();
		$tip=$this->add('Tip','regular','Locator')
			->setStaticSource($this->tips)
			->setSection($this->api->page?$this->api->page:'Index')
		;
    }
    function layout_Menu(){
        $this->api->layout_Menu();

		$this->elements['menu']=$this->api->getElement('menu');
    }
	function getTipsFromTemplate(){
		$template=$this->add('SMlite');
		$template->loadTemplate('tipmanager');
		$tips=explode("[/row]",$template->get('SelfTips'));
		$result=array();
		foreach($tips as $tip){
			$tip=split(';',$tip);
			$result[]=array(
				'id'=>trim($tip[0]),
				'type'=>trim($tip[1]),
				'title'=>trim($tip[2]),
				'tip'=>trim($tip[3]),
				'sections'=>trim($tip[4]),
				'ord'=>trim($tip[5])
			);
		}
		return $result;
	}
	function defaultTemplate(){
        return array('shared','_top');
	}
	function page_Index($p){
		$filter=$p->frame('Content','Quick search')->add('TipFilter',null,'content');
		$grid=$this->add('Grid',null,'Content');
		$grid
			->addColumn('text','type','Type')
			->addColumn('shorttext','sections','Used in sections')
			->addColumn('shorttext,wrap','tip','Text')
			
			->setSource('tip')
		;
		$grid->addButton('Add new')->redirect('EditTip');
		$grid->add('Paginator',null,'paginator');
		$filter->useDQ($grid->dq);
	}
	function page_EditTip($p){
		$form=$p->frame('Content','Tip params')->add('Form',null,'content');
		$form
			->addField('dropdown','type','Tip type')->setValueList($this->tip_types)
			->addField('text','tip','Text')->setNotNull()
			->addField('line','sections','Used in sections')
			
			->setSource('tip')
			->addConditionFromGET('id')
			
			->addSubmit('Save')
			->addButton('Cancel')->redirect('Index')
		;
		if($form->isSubmitted()){
			$form->update();
			$this->api->redirect('Index');
		}
	}
}
class TipFilter extends Filter{
	function init(){
		parent::init();
		$this
			->addField('dropdown','type','Tip type')->setValueList($this->owner->owner->owner->tip_types)
			->addField('line','tip','Text')->setNoSave()
			
			->setSource('tip')
			
			->addSubmit('Show')
			->addButton('Clear')
		;
	}
	function applyDQ($dq){
		parent::applyDQ($dq);
		if($this->get('tip'))$this->dq->where("tip like '%".$this->get('tip')."%'");
	}
}