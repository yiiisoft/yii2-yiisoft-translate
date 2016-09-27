<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

use Yii;

/**
 * Object is the base class that implements the *property* feature.
 *
 * A property is defined by a getter method (e.g. `getLabel`), and/or a setter method (e.g. `setLabel`). For example,
 * the following getter and setter methods define a property named `label`:
 * 一个属性被定义为一个getter方法(如。“getLabel”),和/或setter方法(如。“setLabel”)。例如,下面的getter和setter方法定义一个属性命名为“标签”:
 *
 *
 * ```php
 * private $_label;
 *
 * public function getLabel()
 * {
 *     return $this->_label;
 * }
 *
 * public function setLabel($value)
 * {
 *     $this->_label = $value;
 * }
 * ```
 *
 * Property names are *case-insensitive*.   属性名是不区分大小写的。
 *
 * A property can be accessed like a member variable of an object. Reading or writing a property will cause the invocation
 * of the corresponding getter or setter method. For example,
 * 一个属性可以像访问对象的成员变量。读或写一个属性将导致相应的getter或setter方法的调用。例如,
 *
 * ```php
 * // equivalent to $label = $object->getLabel();   相当于 $label = $object->getLabel();
 * $label = $object->label;
 * // equivalent to $object->setLabel('abc');   相当于 $object->setLabel('abc');
 * $object->label = 'abc';
 * ```
 *
 * If a property has only a getter method and has no setter method, it is considered as *read-only*. In this case, trying
 * to modify the property value will cause an exception.
 * 如果一个属性只有一个getter方法,没有setter方法,它被认为是“只读”。在这种情况下,试图修改属性值将导致异常。
 *
 * One can call [[hasProperty()]], [[canGetProperty()]] and/or [[canSetProperty()]] to check the existence of a property.
 *
 * Besides the property feature, Object also introduces an important object initialization life cycle. In particular,
 * creating an new instance of Object or its derived class will involve the following life cycles sequentially:
 * 除了属性这特点，还引入了一个重要的对象初始化生命周期。特别是,创建一个新实例的对象或其派生类将涉及以下顺序生命周期:
 *
 * 1. the class constructor is invoked; 调用类的构造函数
 * 2. object properties are initialized according to the given configuration; 根据给定的对象属性初始化配置
 * 3. the `init()` method is invoked.   调用init()方法
 *
 * In the above, both Step 2 and 3 occur at the end of the class constructor. It is recommended that
 * you perform object initialization in the `init()` method because at that stage, the object configuration
 * is already applied.
 * 在上面的步骤2和3发生在类构造函数的结束。建议您执行对象初始化的init()因为在那个阶段的方法,对象配置已经应用。
 *
 * In order to ensure the above life cycles, if a child class of Object needs to override the constructor,
 * it should be done like the following:
 * 为了确保上面的生命周期中,如果一个子类的对象需要覆盖的构造函数,它应该如下:
 *
 * ```php
 * public function __construct($param1, $param2, ..., $config = [])
 * {
 *     ...
 *     parent::__construct($config);
 * }
 * ```
 *
 * That is, a `$config` parameter (defaults to `[]`) should be declared as the last parameter
 * of the constructor, and the parent implementation should be called at the end of the constructor.
 * `$config` 参数(默认值 `[]`)应该声明为最后一个参数的构造函数,在最后实现父类的构造函数。
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Object implements Configurable
{
    /**
     * Returns the fully qualified name of this class.  返回这个类的完全限定名称（就是返回全名包括命名空间）
     * @return string the fully qualified name of this class.
     */
    public static function className()
    {
        return get_called_class();  //后期静态绑定
    }

    /**
     * Constructor. 构造函数
     * The default implementation does two things:  默认做了两件事
     *
     * - Initializes the object with the given configuration `$config`.  用给定的配置初始化对象 `$config`
     * - Call [[init()]].  调用 init() 方法
     *
     * If this method is overridden in a child class, it is recommended that    如果该方法被子类重写，那么推荐这样
     *
     * - the last parameter of the constructor is a configuration array, like `$config` here.   构造函数的最后一个参数是配置的数组，像这的 `$config`
     * - call the parent implementation at the end of the constructor.  调用父类实现的构造函数
     *
     * @param array $config name-value pairs that will be used to initialize the object properties  键值对数组将被用于对象的初始化
     */
    public function __construct($config = [])
    {
        if (!empty($config)) {
            Yii::configure($this, $config);
        }
        $this->init();
    }

    /**
     * Initializes the object.  初始化对象
     * This method is invoked at the end of the constructor after the object is initialized with the
     * given configuration.
     * 该方法将在对象实例化并且有配置参数后在构造函数的最后调用
     */
    public function init()
    {
    }

    /**
     * Returns the value of an object property.  返回一个对象属性的值
     *
     * Do not call this method directly as it is a PHP magic method that
     * will be implicitly called when executing `$value = $object->property;`.
     * 不要直接调用这个方法，因为那是PHP的魔术方法，会被隐式的调用执行 `$value = $object->property;`
     *
     * @param string $name the property name    $name 属性名
     * @return mixed the property value
     * @throws UnknownPropertyException if the property is not defined  如果没有定义该属性抛出 UnknownPropertyException
     * @throws InvalidCallException if the property is write-only   如果该属性是只写属性抛出 InvalidCallException
     * @see __set()
     */
    public function __get($name)
    {
        $getter = 'get' . $name;
        if (method_exists($this, $getter)) {
            return $this->$getter();
        } elseif (method_exists($this, 'set' . $name)) {
            throw new InvalidCallException('Getting write-only property: ' . get_class($this) . '::' . $name);
        } else {
            throw new UnknownPropertyException('Getting unknown property: ' . get_class($this) . '::' . $name);
        }
    }

    /**
     * Sets value of an object property.    设置对象的属性值
     *
     * Do not call this method directly as it is a PHP magic method that
     * will be implicitly called when executing `$object->property = $value;`.
     * @param string $name the property name or the event name  字符串 $name 属性名或事件的名称
     * @param mixed $value the property value
     * @throws UnknownPropertyException if the property is not defined
     * @throws InvalidCallException if the property is read-only    如果该属性是只读属性抛出 InvalidCallException
     * @see __get()
     */
    public function __set($name, $value)
    {
        $setter = 'set' . $name;
        if (method_exists($this, $setter)) {
            $this->$setter($value);
        } elseif (method_exists($this, 'get' . $name)) {
            throw new InvalidCallException('Setting read-only property: ' . get_class($this) . '::' . $name);
        } else {
            throw new UnknownPropertyException('Setting unknown property: ' . get_class($this) . '::' . $name);
        }
    }

    /**
     * Checks if a property is set, i.e. defined and not null.  检查如果属性设置,即定义和 not null
     *
     * Do not call this method directly as it is a PHP magic method that
     * will be implicitly called when executing `isset($object->property)`.
     * 不要直接调用这个方法,因为它是一个PHP魔术方法时将隐式地调用执行 `isset($object->property)`
     *
     * Note that if the property is not defined, false will be returned.    注意,如果该属性没有定义,将返回false。
     * @param string $name the property name or the event name  字符串 $name 属性名或事件的名称
     * @return boolean whether the named property is set (not null).  判断该属性是否设置（为 null）
     * @see http://php.net/manual/en/function.isset.php
     */
    public function __isset($name)
    {
        $getter = 'get' . $name;
        if (method_exists($this, $getter)) {
            return $this->$getter() !== null;
        } else {
            return false;
        }
    }

    /**
     * Sets an object property to null.  对象属性设置为null
     *
     * Do not call this method directly as it is a PHP magic method that
     * will be implicitly called when executing `unset($object->property)`.
     *
     * Note that if the property is not defined, this method will do nothing.
     * If the property is read-only, it will throw an exception.    注意,如果没有定义的属性,该方法将什么也不做
     * @param string $name the property name    属性名称
     * @throws InvalidCallException if the property is read only.   如果该属性是只读抛出 InvalidCallException 异常
     * @see http://php.net/manual/en/function.unset.php
     */
    public function __unset($name)
    {
        $setter = 'set' . $name;
        if (method_exists($this, $setter)) {
            $this->$setter(null);
        } elseif (method_exists($this, 'get' . $name)) {
            throw new InvalidCallException('Unsetting read-only property: ' . get_class($this) . '::' . $name);
        }
    }

    /**
     * Calls the named method which is not a class method.  调用一个该类没有的方法
     *
     * Do not call this method directly as it is a PHP magic method that
     * will be implicitly called when an unknown method is being invoked.
     * @param string $name the method name
     * @param array $params method parameters
     * @throws UnknownMethodException when calling unknown method
     * @return mixed the method return value
     */
    public function __call($name, $params)
    {
        throw new UnknownMethodException('Calling unknown method: ' . get_class($this) . "::$name()");
    }

    /**
     * Returns a value indicating whether a property is defined.
     * A property is defined if:
     *
     * - the class has a getter or setter method associated with the specified name
     *   (in this case, property name is case-insensitive);
     * - the class has a member variable with the specified name (when `$checkVars` is true);
     *
     * @param string $name the property name
     * @param boolean $checkVars whether to treat member variables as properties
     * @return boolean whether the property is defined
     * @see canGetProperty()
     * @see canSetProperty()
     */
    public function hasProperty($name, $checkVars = true)
    {
        return $this->canGetProperty($name, $checkVars) || $this->canSetProperty($name, false);
    }

    /**
     * Returns a value indicating whether a property can be read.   返回一个值判断该属性事都可以被读取
     * A property is readable if:
     *
     * - the class has a getter method associated with the specified name  该方法有一个 getter 方法于给定的 name 关联
     *   (in this case, property name is case-insensitive);  不区分大小写
     * - the class has a member variable with the specified name (when `$checkVars` is true);
     *
     * @param string $name the property name
     * @param boolean $checkVars whether to treat member variables as properties
     * @return boolean whether the property can be read
     * @see canSetProperty()
     */
    public function canGetProperty($name, $checkVars = true)
    {
        return method_exists($this, 'get' . $name) || $checkVars && property_exists($this, $name);
    }

    /**
     * Returns a value indicating whether a property can be set.    返回一个值表示这属性是否可以被设置
     * A property is writable if:   如果属性是可写的
     *
     * - the class has a setter method associated with the specified name   该方法有一个 setter 方法于给定的 name 关联
     *   (in this case, property name is case-insensitive);  在这种情况下,属性名不区分大小写
     * - the class has a member variable with the specified name (when `$checkVars` is true);  该类有一个成员变量和给定的 name (当 `$checkVars` 是 true )
     *
     * @param string $name the property name
     * @param boolean $checkVars whether to treat member variables as properties
     * @return boolean whether the property can be written
     * @see canGetProperty()
     */
    public function canSetProperty($name, $checkVars = true)
    {
        return method_exists($this, 'set' . $name) || $checkVars && property_exists($this, $name);
    }

    /**
     * Returns a value indicating whether a method is defined.
     *
     * The default implementation is a call to php function `method_exists()`.
     * You may override this method when you implemented the php magic method `__call()`.
     * @param string $name the method name
     * @return boolean whether the method is defined
     */
    public function hasMethod($name)
    {
        return method_exists($this, $name);
    }
}
