<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
   * LimeSurvey
   * Copyright (C) 2013 The LimeSurvey Project Team / Carsten Schmitz
   * All rights reserved.
   * License: GNU/GPL License v2 or later, see LICENSE.php
   * LimeSurvey is free software. This version may have been modified pursuant
   * to the GNU General Public License, and as distributed it includes or
   * is derivative of works licensed under the GNU General Public License or
   * other free or open source software licenses.
   * See COPYRIGHT.php for copyright notices and details.
   *
*/

class QuestionAttribute extends LSActiveRecord
{
	/**
	 * Returns the static model of Settings table
	 *
	 * @static
	 * @access public
     * @param string $class
	 * @return CActiveRecord
	 */
	public static function model($class = __CLASS__)
	{
		return parent::model($class);
	}

	/**
	 * Returns the setting's table name to be used by the model
	 *
	 * @access public
	 * @return string
	 */
	public function tableName()
	{
		return '{{question_attributes}}';
	}

	/**
	 * Returns the primary key of this table
	 *
	 * @access public
	 * @return string
	 */
	public function primaryKey()
	{
		return 'qaid';
	}

    /**
    * Defines the relations for this model
    *
    * @access public
    * @return array
    */
    public function relations()
    {
		$alias = $this->getTableAlias();
        return array(
        'qid' => array(self::HAS_ONE, 'Questions', '',
            'on' => "$alias.qid = questions.qid",
            ),
        );
    }

    /**
    * Returns this model's validation rules
    *
    */
    public function rules()
    {
        return array(
            array('qid,attribute','required'),
            array('value','LSYii_Validators'),
        );
    }
    
    public function setQuestionAttribute($iQuestionID, $sAttributeName, $sValue)
    {
        xdebug_break();
        Yii::app()->cache->delete("question_attr_$iQuestionID");
        $oModel = new self;
        $oModel->updateAll(
            array('value'=>$sValue),
            'attribute=:attributeName and qid=:questionID',
            array(':attributeName'=>$sAttributeName,':questionID'=>$iQuestionID)
        );


        return Yii::app()->db->createCommand()
            ->select()
            ->from($this->tableName())
            ->where(array('and', 'qid=:qid'))->bindParam(":qid", $qid)
            ->order('qaid asc')
            ->query();
    }    

    /**
    * Returns Question attribute array name=>value
    *
    * @param int $iQuestionID
    * @return array
    */
    public function getQuestionAttributes($iQuestionID) {
        xdebug_break();
        $iQuestionID=(int)$iQuestionID;

        $cacheKey = "question_attr_$iQuestionID";
        $attrs = Yii::app()->cache->get($cacheKey);
        if( $attrs !== false ) {
            return $attrs;
        }

        $attrs = array();

        // Maybe take parent_qid attribute before this qid attribute
        $oQuestion = Question::model()->find("qid=:qid", array('qid'=>$iQuestionID)); 
        if ( !$oQuestion ) {
            return false;
        }

        $aLanguages = array_merge(
            array(Survey::model()->findByPk($oQuestion->sid)->language), 
            Survey::model()->findByPk($oQuestion->sid)->additionalLanguages
        );

        // Get all atributes for this question
        $aAttributeNames = questionAttributes();
        $aAttributeNames = $aAttributeNames[$oQuestion->type];
        $oAttributeValues = QuestionAttribute::model()->findAll(
            "qid=:qid", array('qid'=>$iQuestionID)
        );

        //TODO: generalize to lang retriving function
        foreach($oAttributeValues as $oAttributeValue) {
            if ($oAttributeValue->language) {
                $aAttributeValues[$oAttributeValue->attribute][$oAttributeValue->language]=$oAttributeValue->value;
            } else {
                $aAttributeValues[$oAttributeValue->attribute]=$oAttributeValue->value;
            }
        }

        // Fill with aQuestionAttributes with default attribute or with aAttributeValues
        // Can not use array_replace due to i18n
        foreach($aAttributeNames as $aAttribute) {
            if ($aAttribute['i18n'] == false) {
                if(isset($aAttributeValues[$aAttribute['name']])) {
                    $attrs[$aAttribute['name']]=$aAttributeValues[$aAttribute['name']];
                } else {
                    $attrs[$aAttribute['name']]=$aAttribute['default'];
                }
            } else {
                foreach ($aLanguages as $sLanguage) {
                    if (isset($aAttributeValues[$aAttribute['name']][$sLanguage])) {
                        $attrs[$aAttribute['name']][$sLanguage] = $aAttributeValues[$aAttribute['name']][$sLanguage];
                    } else {
                        $attrs[$aAttribute['name']][$sLanguage] = $aAttribute['default'];
                    }
                }
            }
        }

        Yii::app()->cache->set($cacheKey, $attrs);
        return $attrs;
    }

	public static function insertRecords($data) {
        $attrib = new self;
		foreach ($data as $k => $v)
			$attrib->$k = $v;
		return $attrib->save();
    }

    public function getQuestionsForStatistics($fields, $condition, $orderby=FALSE)
    {
        $command = Yii::app()->db->createCommand()
        ->select($fields)
        ->from($this->tableName())
        ->where($condition);
        if ($orderby != FALSE)
        {
            $command->order($orderby);
        }
        return $command->queryAll();
    }
}
?>
