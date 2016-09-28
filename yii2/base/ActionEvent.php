<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

/**
 * ActionEvent represents the event parameter used for an action event.
 * ActionEvent代表事件参数用于一个动作事件。
 *
 * By setting the [[isValid]] property, one may control whether to continue running the action.
 * 通过设置 [[isValid]] 属性，可以控制是否继续运行 action
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ActionEvent extends Event
{
    /**
     * @var Action the action currently being executed
     * 目前正在执行的行动
     */
    public $action;
    /**
     * @var mixed the action result. Event handlers may modify this property to change the action result.
     * 事件处理程序可以通过修改属性来改变 action 的结果
     */
    public $result;
    /**
     * @var boolean whether to continue running the action. Event handlers of
     * [[Controller::EVENT_BEFORE_ACTION]] may set this property to decide whether
     * to continue running the current action.
     * 是否继续运行该 action ，事件处理程序的 [[Controller::EVENT_BEFORE_ACTION]] 可以设置这个属性来决定是否继续运行当前的行动。
     */
    public $isValid = true;


    /**
     * Constructor.
     * @param Action $action the action as  sociated with this action event.  和该 action 相关联的事件
     * @param array $config name-value pairs that will be used to initialize the object properties
     */
    public function __construct($action, $config = [])
    {
        $this->action = $action;
        parent::__construct($config);
    }
}
