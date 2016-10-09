<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

/**
 * ActionFilter is the base class for action filters.
 * 是所有动作过滤器的基类
 *
 * An action filter will participate in the action execution workflow by responding to
 * the `beforeAction` and `afterAction` events triggered by modules and controllers.
 * 一个行动过滤器将参与行动执行工作流的回应“beforeAction”和“afterAction”事件引发的模块和控制
 *
 * Check implementation of [[\yii\filters\AccessControl]], [[\yii\filters\PageCache]] and [[\yii\filters\HttpCache]] as examples on how to use it.
 * 检查如何实现 [[\yii\filters\AccessControl]], [[\yii\filters\PageCache]] 和 [[\yii\filters\HttpCache]]
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ActionFilter extends Behavior
{
    /**
     * @var array list of action IDs that this filter should apply to. If this property is not set,
     * then the filter applies to all actions, unless they are listed in [[except]].
     * 在该数组中的 action IDs 会被接受，如果该属性没有设置则会接受所有的 action。除非他们在 [[except]] 中
     * If an action ID appears in both [[only]] and [[except]], this filter will NOT apply to it.
     * 如果一个 action ID 同时在 [[only]] 和 [[except]], 该过滤器不会接受它
     *
     * Note that if the filter is attached to a module, the action IDs should also include child module IDs (if any)
     * and controller IDs.
     * 注意,如果过滤器是附加到模块,action IDs 还应该包括子模块 IDs(如果有的话)和控制器 IDs
     *
     * @see except
     */
    public $only;
    /**
     * @var array list of action IDs that this filter should not apply to.
     * 该数组中的 action IDs 不会被过滤器接受
     * @see only
     */
    public $except = [];


    /**
     * @inheritdoc
     */
    public function attach($owner)
    {
        $this->owner = $owner;
        $owner->on(Controller::EVENT_BEFORE_ACTION, [$this, 'beforeFilter']);
    }

    /**
     * @inheritdoc
     */
    public function detach()
    {
        if ($this->owner) {
            $this->owner->off(Controller::EVENT_BEFORE_ACTION, [$this, 'beforeFilter']);
            $this->owner->off(Controller::EVENT_AFTER_ACTION, [$this, 'afterFilter']);
            $this->owner = null;
        }
    }

    /**
     * @param ActionEvent $event
     */
    public function beforeFilter($event)
    {
        if (!$this->isActive($event->action)) {
            return;
        }

        $event->isValid = $this->beforeAction($event->action);
        if ($event->isValid) {
            // call afterFilter only if beforeFilter succeeds
            // beforeFilter and afterFilter should be properly nested
            $this->owner->on(Controller::EVENT_AFTER_ACTION, [$this, 'afterFilter'], null, false);
        } else {
            $event->handled = true;
        }
    }

    /**
     * @param ActionEvent $event
     */
    public function afterFilter($event)
    {
        $event->result = $this->afterAction($event->action, $event->result);
        $this->owner->off(Controller::EVENT_AFTER_ACTION, [$this, 'afterFilter']);
    }

    /**
     * This method is invoked right before an action is to be executed (after all possible filters.)
     * You may override this method to do last-minute preparation for the action.
     * @param Action $action the action to be executed.
     * @return boolean whether the action should continue to be executed.
     */
    public function beforeAction($action)
    {
        return true;
    }

    /**
     * This method is invoked right after an action is executed.
     * You may override this method to do some postprocessing for the action.
     * @param Action $action the action just executed.
     * @param mixed $result the action execution result
     * @return mixed the processed action result.
     */
    public function afterAction($action, $result)
    {
        return $result;
    }

    /**
     * Returns an action ID by converting [[Action::$uniqueId]] into an ID relative to the module
     * @param Action $action
     * @return string
     * @since 2.0.7
     */
    protected function getActionId($action) {
        if ($this->owner instanceof Module) {
            $mid = $this->owner->getUniqueId();
            $id = $action->getUniqueId();
            if ($mid !== '' && strpos($id, $mid) === 0) {
                $id = substr($id, strlen($mid) + 1);
            }
        } else {
            $id = $action->id;
        }

        return $id;
    }

    /**
     * Returns a value indicating whether the filter is active for the given action.
     * @param Action $action the action being filtered
     * @return boolean whether the filter is active for the given action.
     */
    protected function isActive($action)
    {
        $id = $this->getActionId($action);
        return !in_array($id, $this->except, true) && (empty($this->only) || in_array($id, $this->only, true));
    }
}
