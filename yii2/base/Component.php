<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

use Yii;

/**
 * Component is the base class that implements the *property*, *event* and *behavior* features.
 * 组件的基类实现了*property*, *event*, *behavior* 特性
 *
 * Component provides the *event* and *behavior* features, in addition to the *property* feature which is implemented in
 * its parent class [[Object]].
 * 组件提供*event* 和 *behavior*特性，除了 *property* 功能特性,因为已经通过父类 Object 实现
 *
 * Event is a way to "inject" custom code into existing code at certain places. For example, a comment object can trigger
 * an "add" event when the user adds a comment. We can write custom code and attach it to this event so that when the event
 * is triggered (i.e. comment will be added), our custom code will be executed.
 * 事件是一种将自定义代码 "注入" 到现有代码中在某些地方。例如,一个对象可以触发一个 "添加" 事件发表看法时用户添加了一个评论。
 * 我们可以编写自定义代码,将它附加到这个事件,当事件被触发时(即添加评论),我们将执行自定义代码。
 *
 * An event is identified by a name that should be unique within the class it is defined at. Event names are *case-sensitive*.
 * 一个事件是由一个名字标识,在它被定义的类中应该是惟一的。事件名称是 区分大小写的
 *
 * One or multiple PHP callbacks, called *event handlers*, can be attached to an event. You can call [[trigger()]] to
 * raise an event. When an event is raised, the event handlers will be invoked automatically in the order they were
 * attached.
 * 一个或多个PHP回调，调用 *event handlers* ，可以被附加到一个事件。你可以调用 [[trigger()]] 出发一个事件。当一个事件被触发，那个事件处理程序将自动调用它们的顺序。
 *
 * To attach an event handler to an event, call [[on()]]:   添加一个事件处理程序到事件中
 *
 * ```php
 * $post->on('update', function ($event) {
 *     // send email notification
 * });
 * ```
 *
 * In the above, an anonymous function is attached to the "update" event of the post. You may attach
 * the following types of event handlers:
 * 在上面,一个匿名函数附加到事件的 "更新"。你可以附上以下类型的事件处理程序:
 *
 * - anonymous function: `function ($event) { ... }`  匿名函数
 * - object method: `[$object, 'handleAdd']`  对象函数
 * - static class method: `['Page', 'handleAdd']`  静态类方法
 * - global function: `'handleAdd'`  全局函数
 *
 * The signature of an event handler should be like the following:
 *
 * ```php
 * function foo($event)
 * ```
 *
 * where `$event` is an [[Event]] object which includes parameters associated with the event.
 * `$event` 是一个 [[Event]] 对象包括与事件相关的参数
 *
 * You can also attach a handler to an event when configuring a component with a configuration array.
 * 你也可以附加一个事件处理程序,当配置一个组件与一个配置数组。
 * The syntax is like the following:  语法就像下面
 *
 * ```php
 * [
 *     'on add' => function ($event) { ... }
 * ]
 * ```
 *
 * where `on add` stands for attaching an event to the `add` event.
 * `on add` 代表将添加一个事件到 `add` 事件里
 *
 * Sometimes, you may want to associate extra data with an event handler when you attach it to an event
 * and then access it when the handler is invoked. You may do so by
 * 有时,你可能想要把额外的数据与一个事件处理程序,当你将它附加到一个事件,然后调用处理程序时访问它。你可以这样做
 *
 * ```php
 * $post->on('update', function ($event) {
 *     // the data can be accessed via $event->data
 * }, $data);
 * ```
 *
 * A behavior is an instance of [[Behavior]] or its child class. A component can be attached with one or multiple
 * behaviors. When a behavior is attached to a component, its public properties and methods can be accessed via the
 * component directly, as if the component owns those properties and methods.
 * 一个行为是 [[Behavior]] 的实例或其子类。一个组件可以与一个或多个附加行为。当一个行为被添加到一个组件中，
 * 它的公共属性和方法直接可以通过访问组件，如果组件拥有这些属性和方法。
 *
 * To attach a behavior to a component, declare it in [[behaviors()]], or explicitly call [[attachBehavior]]. Behaviors
 * declared in [[behaviors()]] are automatically attached to the corresponding component.
 *
 * One can also attach a behavior to a component when configuring it with a configuration array. The syntax is like the
 * following:
 * 还可以附加一个行为一个组件在配置配置阵列。语法就像下面
 *
 * ```php
 * [
 *     'as tree' => [
 *         'class' => 'Tree',
 *     ],
 * ]
 * ```
 *
 * where `as tree` stands for attaching a behavior named `tree`, and the array will be passed to [[\Yii::createObject()]]
 * to create the behavior object.
 * 在 `as tree` 代表将一个名为 `tree` 的行为,和数组将被传递给 [[\Yii::createObject()]] 来创建对象的行为。
 *
 * @property Behavior[] $behaviors List of behaviors attached to this component. This property is read-only.  $behaviors 行为附加到该组件的列表。这个属性是只读的。
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Component extends Object
{
    /**
     * @var array the attached event handlers (event name => handlers)
     */
    private $_events = [];
    /**
     * @var Behavior[]|null the attached behaviors (behavior name => behavior). This is `null` when not initialized.
     */
    private $_behaviors;


    /**
     * Returns the value of a component property.  返回一个组件属性的值
     * This method will check in the following order and act accordingly:
     * 该方法将按照以下顺序检查并采取相应行动:
     *
     *  - a property defined by a getter: return the getter result
     * 属性定义为 getter : 返回 getter 的结果
     *  - a property of a behavior: return the behavior property value
     * 返回 behavior 属性
     *
     * Do not call this method directly as it is a PHP magic method that
     * will be implicitly called when executing `$value = $component->property;`.
     * @param string $name the property name
     * @return mixed the property value or the value of a behavior's property
     * @throws UnknownPropertyException if the property is not defined
     * @throws InvalidCallException if the property is write-only.
     * @see __set()
     */
    public function __get($name)
    {
        $getter = 'get' . $name;
        if (method_exists($this, $getter)) {
            // read property, e.g. getName()
            return $this->$getter();
        } else {
            // behavior property
            $this->ensureBehaviors();
            foreach ($this->_behaviors as $behavior) {
                if ($behavior->canGetProperty($name)) {
                    return $behavior->$name;
                }
            }
        }
        if (method_exists($this, 'set' . $name)) {
            throw new InvalidCallException('Getting write-only property: ' . get_class($this) . '::' . $name);
        } else {
            throw new UnknownPropertyException('Getting unknown property: ' . get_class($this) . '::' . $name);
        }
    }

    /**
     * Sets the value of a component property.
     * This method will check in the following order and act accordingly:
     *
     *  - a property defined by a setter: set the property value
     *  - an event in the format of "on xyz": attach the handler to the event "xyz"
     *  - a behavior in the format of "as xyz": attach the behavior named as "xyz"
     *  - a property of a behavior: set the behavior property value
     *
     * Do not call this method directly as it is a PHP magic method that
     * will be implicitly called when executing `$component->property = $value;`.
     * @param string $name the property name or the event name
     * @param mixed $value the property value
     * @throws UnknownPropertyException if the property is not defined
     * @throws InvalidCallException if the property is read-only.
     * @see __get()
     */
    public function __set($name, $value)
    {
        $setter = 'set' . $name;
        if (method_exists($this, $setter)) {
            // set property
            $this->$setter($value);

            return;
        } elseif (strncmp($name, 'on ', 3) === 0) {
            // on event: attach event handler
            $this->on(trim(substr($name, 3)), $value);

            return;
        } elseif (strncmp($name, 'as ', 3) === 0) {
            // as behavior: attach behavior
            $name = trim(substr($name, 3));
            $this->attachBehavior($name, $value instanceof Behavior ? $value : Yii::createObject($value));

            return;
        } else {
            // behavior property
            $this->ensureBehaviors();
            foreach ($this->_behaviors as $behavior) {
                if ($behavior->canSetProperty($name)) {
                    $behavior->$name = $value;

                    return;
                }
            }
        }
        if (method_exists($this, 'get' . $name)) {
            throw new InvalidCallException('Setting read-only property: ' . get_class($this) . '::' . $name);
        } else {
            throw new UnknownPropertyException('Setting unknown property: ' . get_class($this) . '::' . $name);
        }
    }

    /**
     * Checks if a property is set, i.e. defined and not null.
     * This method will check in the following order and act accordingly:
     *
     *  - a property defined by a setter: return whether the property is set
     *  - a property of a behavior: return whether the property is set
     *  - return `false` for non existing properties
     *
     * Do not call this method directly as it is a PHP magic method that
     * will be implicitly called when executing `isset($component->property)`.
     * @param string $name the property name or the event name
     * @return boolean whether the named property is set
     * @see http://php.net/manual/en/function.isset.php
     */
    public function __isset($name)
    {
        $getter = 'get' . $name;
        if (method_exists($this, $getter)) {
            return $this->$getter() !== null;
        } else {
            // behavior property
            $this->ensureBehaviors();
            foreach ($this->_behaviors as $behavior) {
                if ($behavior->canGetProperty($name)) {
                    return $behavior->$name !== null;
                }
            }
        }
        return false;
    }

    /**
     * Sets a component property to be null.
     * This method will check in the following order and act accordingly:
     *
     *  - a property defined by a setter: set the property value to be null
     *  - a property of a behavior: set the property value to be null
     *
     * Do not call this method directly as it is a PHP magic method that
     * will be implicitly called when executing `unset($component->property)`.
     * @param string $name the property name
     * @throws InvalidCallException if the property is read only.
     * @see http://php.net/manual/en/function.unset.php
     */
    public function __unset($name)
    {
        $setter = 'set' . $name;
        if (method_exists($this, $setter)) {
            $this->$setter(null);
            return;
        } else {
            // behavior property
            $this->ensureBehaviors();
            foreach ($this->_behaviors as $behavior) {
                if ($behavior->canSetProperty($name)) {
                    $behavior->$name = null;
                    return;
                }
            }
        }
        throw new InvalidCallException('Unsetting an unknown or read-only property: ' . get_class($this) . '::' . $name);
    }

    /**
     * Calls the named method which is not a class method.  调用一个该类不存在的方法
     *
     * This method will check if any attached behavior has
     * the named method and will execute it if available.
     * 该方法将会检查如果有任何附加的可使用行为将被执行
     *
     * Do not call this method directly as it is a PHP magic method that
     * will be implicitly called when an unknown method is being invoked.
     * 不要直接调用这个方法，因为它是PHP的魔术方法。调用一个未知的方法将自动被调用
     *
     * @param string $name the method name  方法名
     * @param array $params method parameters   方法参数
     * @return mixed the method return value    方法的返回值
     * @throws UnknownMethodException when calling unknown method   当没有该方法时抛出 UnknownMethodException
     */
    public function __call($name, $params)
    {
        $this->ensureBehaviors();
        foreach ($this->_behaviors as $object) {
            if ($object->hasMethod($name)) {
                return call_user_func_array([$object, $name], $params);
            }
        }
        throw new UnknownMethodException('Calling unknown method: ' . get_class($this) . "::$name()");
    }

    /**
     * This method is called after the object is created by cloning an existing one.    该方法将在对象被克隆时被调用
     * It removes all behaviors because they are attached to the old object.    它删除所有的行为,因为他们是在旧的对象
     */
    public function __clone()
    {
        $this->_events = [];
        $this->_behaviors = null;
    }

    /**
     * Returns a value indicating whether a property is defined for this component.  返回一个值表明是否为该组件定义属性
     * A property is defined if:    如果一个属性被定义:
     *
     * - the class has a getter or setter method associated with the specified name  该类有一个 getter 或者 setter 方法时和 $name 相关联的
     *   (in this case, property name is case-insensitive);  在这种情况下，属性名不区分大小写
     * - the class has a member variable with the specified name (when `$checkVars` is true);  该类有一个成员变量和指定的名称（当 `$checkVars` 为 true）
     * - an attached behavior has a property of the given name (when `$checkBehaviors` is true).  有一个给定的名称在附加的行为中属性中
     *
     * @param string $name the property name
     * @param boolean $checkVars whether to treat member variables as properties
     * @param boolean $checkBehaviors whether to treat behaviors' properties as properties of this component
     * @return boolean whether the property is defined
     * @see canGetProperty()
     * @see canSetProperty()
     */
    public function hasProperty($name, $checkVars = true, $checkBehaviors = true)
    {
        return $this->canGetProperty($name, $checkVars, $checkBehaviors) || $this->canSetProperty($name, false, $checkBehaviors);
    }

    /**
     * Returns a value indicating whether a property can be read.  返回一个值表名该属性是否可以被读取
     * A property can be read if:
     *
     * - the class has a getter method associated with the specified name
     *   (in this case, property name is case-insensitive);
     * - the class has a member variable with the specified name (when `$checkVars` is true);
     * - an attached behavior has a readable property of the given name (when `$checkBehaviors` is true).
     *
     * @param string $name the property name
     * @param boolean $checkVars whether to treat member variables as properties
     * @param boolean $checkBehaviors whether to treat behaviors' properties as properties of this component
     * @return boolean whether the property can be read
     * @see canSetProperty()
     */
    public function canGetProperty($name, $checkVars = true, $checkBehaviors = true)
    {
        if (method_exists($this, 'get' . $name) || $checkVars && property_exists($this, $name)) {
            return true;
        } elseif ($checkBehaviors) {
            $this->ensureBehaviors();
            foreach ($this->_behaviors as $behavior) {
                if ($behavior->canGetProperty($name, $checkVars)) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Returns a value indicating whether a property can be set.
     * A property can be written if:
     *
     * - the class has a setter method associated with the specified name
     *   (in this case, property name is case-insensitive);
     * - the class has a member variable with the specified name (when `$checkVars` is true);
     * - an attached behavior has a writable property of the given name (when `$checkBehaviors` is true).
     *
     * @param string $name the property name
     * @param boolean $checkVars whether to treat member variables as properties
     * @param boolean $checkBehaviors whether to treat behaviors' properties as properties of this component
     * @return boolean whether the property can be written
     * @see canGetProperty()
     */
    public function canSetProperty($name, $checkVars = true, $checkBehaviors = true)
    {
        if (method_exists($this, 'set' . $name) || $checkVars && property_exists($this, $name)) {
            return true;
        } elseif ($checkBehaviors) {
            $this->ensureBehaviors();
            foreach ($this->_behaviors as $behavior) {
                if ($behavior->canSetProperty($name, $checkVars)) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Returns a value indicating whether a method is defined.  返回一个值表示该方法是否被定义
     * A method is defined if:  如果一个方法被定义
     *
     * - the class has a method with the specified name  该类有个方法和指定名称
     * - an attached behavior has a method with the given name (when `$checkBehaviors` is true).  在给定的变量中有一个附加的行为方法（当 `$checkBehaviors` 为 true）
     *
     * @param string $name the property name  属性名称
     * @param boolean $checkBehaviors whether to treat behaviors' methods as methods of this component  是否把 behaviors的方法当做该组件的方法
     * @return boolean whether the property is defined  该属性是否被定义
     */
    public function hasMethod($name, $checkBehaviors = true)
    {
        if (method_exists($this, $name)) {
            return true;
        } elseif ($checkBehaviors) {
            $this->ensureBehaviors();
            foreach ($this->_behaviors as $behavior) {
                if ($behavior->hasMethod($name)) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Returns a list of behaviors that this component should behave as.
     *
     * Child classes may override this method to specify the behaviors they want to behave as.
     * 子类可以覆盖该方法去指定 behaviors 为他们想要的那样
     *
     * The return value of this method should be an array of behavior objects or configurations
     * indexed by behavior names. A behavior configuration can be either a string specifying
     * the behavior class or an array of the following structure:
     * 这个方法的返回值应该是一个关于 `behavior` 对象数组或配置索引的行为的名字。行为的配置可以是一个关于behavior类的字符串或者是如下的一个数组:
     *
     * ```php
     * 'behaviorName' => [
     *     'class' => 'BehaviorClass',
     *     'property1' => 'value1',
     *     'property2' => 'value2',
     * ]
     * ```
     *
     * Note that a behavior class must extend from [[Behavior]]. Behavior names can be strings
     * or integers. If the former, they uniquely identify the behaviors. If the latter, the corresponding
     * behaviors are anonymous and their properties and methods will NOT be made available via the component
     * (however, the behaviors can still respond to the component's events).
     * 注意一个行为类必须继承 [[Behavior]]。Behavior名字可以是字符串或整数。如果是前者，他们唯一标识行为。如果是后者，相关联的行为是匿名的，它们不会通过组件提供属性和方法。
     * （但是，行为仍然可以响应组件的事件）
     *
     * Behaviors declared in this method will be attached to the component automatically (on demand).
     * 行为中声明该方法将自动附加到组件(按需)
     *
     * @return array the behavior configurations.
     */
    public function behaviors()
    {
        return [];
    }

    /**
     * Returns a value indicating whether there is any handler attached to the named event.  返回一个值指示是否有任何 handler 被添加到事件中
     * @param string $name the event name  事件名称
     * @return boolean whether there is any handler attached to the event.  是否有任何 handler（处理程序）被附加到事件中
     */
    public function hasEventHandlers($name)
    {
        $this->ensureBehaviors();
        return !empty($this->_events[$name]) || Event::hasHandlers($this, $name);
    }

    /**
     * Attaches an event handler to an event.  附加一个事件的处理程序到一个事件中
     *
     * The event handler must be a valid PHP callback. The following are
     * some examples:
     * 该事件处理程序必须是一个有效的PHP回调，下面是一些师例：
     *
     * ```
     * function ($event) { ... }         // anonymous function  匿名函数
     * [$object, 'handleClick']          // $object->handleClick()
     * ['Page', 'handleClick']           // Page::handleClick()
     * 'handleClick'                     // global function handleClick()
     * ```
     *
     * The event handler must be defined with the following signature,  事件处理程序必须定义以下签名
     *
     * ```
     * function ($event)
     * ```
     *
     * where `$event` is an [[Event]] object which includes parameters associated with the event.
     * `$event` 是一个 [[Event]] 对象包括与事件相关连的参数
     *
     * @param string $name the event name  事件名称
     * @param callable $handler the event handler  事件处理程序
     * @param mixed $data the data to be passed to the event handler when the event is triggered.  当事件被出发 $data 的数据将会传递到处理程序中
     * When the event handler is invoked, this data can be accessed via [[Event::data]].  当事件处理程序被调用，该 data 可以通过 [[Event::data]] 访问
     * @param boolean $append whether to append new event handler to the end of the existing
     * handler list. If false, the new handler will be inserted at the beginning of the existing
     * handler list.
     * 是否附加新的事件处理程序到现有的处理程序列表的最后中。如果是 false ,新的处理程序将插入到现有的处理程序列表的开始。
     *
     * @see off()
     */
    public function on($name, $handler, $data = null, $append = true)
    {
        $this->ensureBehaviors();
        if ($append || empty($this->_events[$name])) {
            $this->_events[$name][] = [$handler, $data];
        } else {
            array_unshift($this->_events[$name], [$handler, $data]);
        }
    }

    /**
     * Detaches an existing event handler from this component.  从该组件分离现有的事件处理程序
     * This method is the opposite of [[on()]].  该方法和 [[on()]] 作用相反
     * @param string $name event name  事件名称
     * @param callable $handler the event handler to be removed.  该事件处理程序将被移除
     * If it is null, all handlers attached to the named event will be removed.  如果是 null 所有的处理程序将被移除
     * @return boolean if a handler is found and detached  如果一个处理程序被找到和分离
     * @see on()
     */
    public function off($name, $handler = null)
    {
        $this->ensureBehaviors();
        if (empty($this->_events[$name])) {
            return false;
        }
        if ($handler === null) {
            unset($this->_events[$name]);
            return true;
        } else {
            $removed = false;
            foreach ($this->_events[$name] as $i => $event) {
                if ($event[0] === $handler) {
                    unset($this->_events[$name][$i]);
                    $removed = true;
                }
            }
            if ($removed) {
                $this->_events[$name] = array_values($this->_events[$name]);
            }
            return $removed;
        }
    }

    /**
     * Triggers an event.  触发一个事件
     * This method represents the happening of an event. It invokes
     * all attached handlers for the event including class-level handlers.
     * 该方法表示一个事件的发生。它调用所有附加的处理程序包括类级别的处理程序
     * @param string $name the event name  事件名称
     * @param Event $event the event parameter. If not set, a default [[Event]] object will be created.
     * 事件参数，如果没有设置，一个默认的 [[Event]] 的对象将会被创建
     */
    public function trigger($name, Event $event = null)
    {
        $this->ensureBehaviors();
        if (!empty($this->_events[$name])) {
            if ($event === null) {
                $event = new Event;
            }
            if ($event->sender === null) {
                $event->sender = $this;
            }
            $event->handled = false;
            $event->name = $name;
            foreach ($this->_events[$name] as $handler) {
                $event->data = $handler[1];
                call_user_func($handler[0], $event);
                // stop further handling if the event is handled
                if ($event->handled) {
                    return;
                }
            }
        }
        // invoke class-level attached handlers
        Event::trigger($this, $name, $event);
    }

    /**
     * Returns the named behavior object.  返回该名称的 behavior 对象
     * @param string $name the behavior name  behavior 名称
     * @return null|Behavior the behavior object, or null if the behavior does not exist  null或Behavior  behavior 对象，或 null 如果 behavior 不存在
     */
    public function getBehavior($name)
    {
        $this->ensureBehaviors();
        return isset($this->_behaviors[$name]) ? $this->_behaviors[$name] : null;
    }

    /**
     * Returns all behaviors attached to this component.  返回该组件中所有的被添加的行为
     * @return Behavior[] list of behaviors attached to this component
     */
    public function getBehaviors()
    {
        $this->ensureBehaviors();
        return $this->_behaviors;
    }

    /**
     * Attaches a behavior to this component.   添加一个行为到该组件中
     * This method will create the behavior object based on the given  该方法将会基于所给的配置创建一个 bahavior 对象
     * configuration. After that, the behavior object will be attached to  在这之后，该 bahavior 对象将会被附加到该组件通过调用 [[Behavior::attach()]] 方法
     * this component by calling the [[Behavior::attach()]] method.
     * @param string $name the name of the behavior.  行为的名称
     * @param string|array|Behavior $behavior the behavior configuration. This can be one of the following:
     *
     *  - a [[Behavior]] object  一个 [[Behavior]] 对象
     *  - a string specifying the behavior class  一个字符串指定 behavior 类
     *  - an object configuration array that will be passed to [[Yii::createObject()]] to create the behavior object.
     * 一个通过 [[Yii::createObject()]] 来创建的的行为对象
     *
     * @return Behavior the behavior object  返回 behavior 对象
     * @see detachBehavior()
     */
    public function attachBehavior($name, $behavior)
    {
        $this->ensureBehaviors();
        return $this->attachBehaviorInternal($name, $behavior);
    }

    /**
     * Attaches a list of behaviors to the component.  附加一个行为的列表到组件中
     * Each behavior is indexed by its name and should be a [[Behavior]] object,  每一个行为都是被编入通过它的名字和应该是个 [[Behavior]] 对象
     * a string specifying the behavior class, or an configuration array for creating the behavior.  一个字符串指定行为类或一个配置数组来创建行为
     * @param array $behaviors list of behaviors to be attached to the component  被添加到组件中的行为列表
     * @see attachBehavior()
     */
    public function attachBehaviors($behaviors)
    {
        $this->ensureBehaviors();
        foreach ($behaviors as $name => $behavior) {
            $this->attachBehaviorInternal($name, $behavior);
        }
    }

    /**
     * Detaches a behavior from the component.  从组件中分离一个行为
     * The behavior's [[Behavior::detach()]] method will be invoked.  行为的 [[Behavior::detach()]] 方法将被调用
     * @param string $name the behavior's name.  行为的名字
     * @return null|Behavior the detached behavior. Null if the behavior does not exist.  被分离的行为。如果行为不存在则返回 null
     */
    public function detachBehavior($name)
    {
        $this->ensureBehaviors();
        if (isset($this->_behaviors[$name])) {
            $behavior = $this->_behaviors[$name];
            unset($this->_behaviors[$name]);
            $behavior->detach();
            return $behavior;
        } else {
            return null;
        }
    }

    /**
     * Detaches all behaviors from the component.  从组件中移除所有行为
     */
    public function detachBehaviors()
    {
        $this->ensureBehaviors();
        foreach ($this->_behaviors as $name => $behavior) {
            $this->detachBehavior($name);
        }
    }

    /**
 * Makes sure that the behaviors declared in [[behaviors()]] are attached to this component.
 * 确保 behaviors 在 [[behaviors()]] 被声明并且已经添加到这组件中的
 */
    public function ensureBehaviors()
    {
        if ($this->_behaviors === null) {
            $this->_behaviors = [];
            foreach ($this->behaviors() as $name => $behavior) {
                $this->attachBehaviorInternal($name, $behavior);
            }
        }
    }

    /**
     * Attaches a behavior to this component.   附加一个行为到该组件上
     * @param string|integer $name the name of the behavior. If this is an integer, it means the behavior
     * is an anonymous one. Otherwise, the behavior is a named one and any existing behavior with the same name
     * will be detached first.
     * 行为的名字。如果是一个整数，它的意思是该行为是匿名的。否则,该行将会附加，若有同名的将会覆盖旧的。
     * @param string|array|Behavior $behavior the behavior to be attached
     * @return Behavior the attached behavior.
     */
    private function attachBehaviorInternal($name, $behavior)
    {
        if (!($behavior instanceof Behavior)) {
            $behavior = Yii::createObject($behavior);
        }
        if (is_int($name)) {
            $behavior->attach($this);
            $this->_behaviors[] = $behavior;
        } else {
            if (isset($this->_behaviors[$name])) {
                $this->_behaviors[$name]->detach();
            }
            $behavior->attach($this);
            $this->_behaviors[$name] = $behavior;
        }
        return $behavior;
    }
}
