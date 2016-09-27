<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

/**
 * Configurable is the interface that should be implemented by classes who support configuring
 * its properties through the last parameter to its constructor.
 * Configurable 接口是通过构造函数的最后一个参数实现类支持配置属性
 *
 * The interface does not declare any method. Classes implementing this interface must declare their constructors
 * like the following:
 * 这个借口不声明任何方法。类实现该借口必须声明他们的构造方法，就像下面：
 *
 * ```php
 * public function __constructor($param1, $param2, ..., $config = [])
 * ```
 *
 * That is, the last parameter of the constructor must accept a configuration array.
 * 就是最后一个参数必须接受一个配置数组
 *
 * This interface is mainly used by [[\yii\di\Container]] so that it can pass object configuration as the
 * last parameter to the implementing class' constructor.
 * 该借口主要用于 [[\yii\di\Container]] ，所以它可以通过对象配置作为最后一个参数来实现类的构造函数。
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0.3
 */
interface Configurable
{
}

