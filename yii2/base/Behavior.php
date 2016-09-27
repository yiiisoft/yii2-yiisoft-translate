<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

/**
 * Behavior is the base class for all behavior classes.  Behavior 是所有行为类的基类
 *
 * A behavior can be used to enhance the functionality of an existing component without modifying its code.
 * 一个行为可以用来增强现有的功能组件,无需修改其代码。
 * In particular, it can "inject" its own methods and properties into the component
 * and make them directly accessible via the component. It can also respond to the events triggered in the component
 * and thus intercept the normal code execution.
 * 特别的是它可以注入自己的方法和属性到组件中，让它们可以直接通过组件使用。它也可以响应事件触发组件，因此拦截正常的代码执行。
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Behavior extends Object
{
    /**
     * @var Component the owner of this behavior
     */
    public $owner;


    /**
     * Declares event handlers for the [[owner]]'s events.  设置事件
     *
     * Child classes may override this method to declare what PHP callbacks should
     * be attached to the events of the [[owner]] component.
     * 子类可以重写该方法来声明什么PHP回调能被添加到 [[owner]] 组件的事件中。
     *
     * The callbacks will be attached to the [[owner]]'s events when the behavior is
     * attached to the owner; and they will be detached from the events when
     * the behavior is detached from the component.
     * 当行为被附加到自身时，该回调会附加到 [[owner]] 的事件中；档行为从组件中移除时它们会被从事件中移除
     *
     * The callbacks can be any of the following:  该回调可以是下面的一些情况
     *
     * - method in this behavior: `'handleClick'`, equivalent to `[$this, 'handleClick']`  行为中的方法
     * - object method: `[$object, 'handleClick']`  对象方法
     * - static method: `['Page', 'handleClick']`   静态方法
     * - anonymous function: `function ($event) { ... }`    匿名函数
     *
     * The following is an example:
     *
     * ```php
     * [
     *     Model::EVENT_BEFORE_VALIDATE => 'myBeforeValidate',
     *     Model::EVENT_AFTER_VALIDATE => 'myAfterValidate',
     * ]
     * ```
     *
     * @return array events (array keys) and the corresponding event handler methods (array values).  事件和相应的事件处理程序方法
     */
    public function events()
    {
        return [];
    }

    /**
     * Attaches the behavior object to the component.  添加行为对象到组件中
     * The default implementation will set the [[owner]] property
     * and attach event handlers as declared in [[events]].
     * 默认实现 [[owner]] 的属性和附加在 [[events]] 中声明的事件处理程序
     * Make sure you call the parent implementation if you override this method.
     * 如果你重写该方法，确保你调用了父类的实现
     * @param Component $owner the component that this behavior is to be attached to.  该行为被添加到组件上
     */
    public function attach($owner)
    {
        $this->owner = $owner;
        foreach ($this->events() as $event => $handler) {
            $owner->on($event, is_string($handler) ? [$this, $handler] : $handler);
        }
    }

    /**
     * Detaches the behavior object from the component.  从组件中移除行为对象
     * The default implementation will unset the [[owner]] property
     * and detach event handlers declared in [[events]].
     * Make sure you call the parent implementation if you override this method.
     */
    public function detach()
    {
        if ($this->owner) {
            foreach ($this->events() as $event => $handler) {
                $this->owner->off($event, is_string($handler) ? [$this, $handler] : $handler);
            }
            $this->owner = null;
        }
    }
}
