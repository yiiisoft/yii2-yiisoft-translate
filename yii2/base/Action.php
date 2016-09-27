<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

use Yii;

/**
 * Action is the base class for all controller action classes.  Action 是所有行为控制器类的基类
 *
 * Action provides a way to reuse action method code. An action method in an Action
 * class can be used in multiple controllers or in different projects.
 * Action 提供了一种操作方法的代码重用。一个操作方法可以在多个控制器或不同的项目中使用。
 *
 * Derived classes must implement a method named `run()`. This method
 * will be invoked by the controller when the action is requested.
 * 派生类必须实现 `run()` 方法。当行为被请求时该方法就会被调用。
 * The `run()` method can have parameters which will be filled up
 * with user input values automatically according to their names.
 * run() 方法会根据用户输入的值自动填写参数
 * For example, if the `run()` method is declared as follows:
 * 例如，如果 `run()` 的方法申明如下：
 *
 * ```php
 * public function run($id, $type = 'book') { ... }
 * ```
 *
 * And the parameters provided for the action are: `['id' => 1]`.
 * 提供给该 action 的参数是 `['id' => 1]`
 * Then the `run()` method will be invoked as `run(1)` automatically.
 * 然后 `run()` 方法将会自动的调用像 `run(1)`
 *
 * @property string $uniqueId The unique ID of this action among the whole application. This property is
 * read-only.
 * 在整个应用程序中这个 action 有惟一的ID。这个属性是只读的。
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class Action extends Component
{
    /**
     * @var string ID of the action  action 的 ID
     */
    public $id;
    /**
     * @var Controller|\yii\web\Controller the controller that owns this action  拥有这个 action 的控制器
     */
    public $controller;


    /**
     * Constructor.
     *
     * @param string $id the ID of this action  这个 action 的 ID
     * @param Controller $controller the controller that owns this action  该控制器将会拥有该 action
     * @param array $config name-value pairs that will be used to initialize the object properties  键值对的数组将在初始化中使用
     */
    public function __construct($id, $controller, $config = [])
    {
        $this->id = $id;
        $this->controller = $controller;
        parent::__construct($config);
    }

    /**
     * Returns the unique ID of this action among the whole application.  返回唯一的 ID 在整个应用中
     *
     * @return string the unique ID of this action among the whole application.
     */
    public function getUniqueId()
    {
        return $this->controller->getUniqueId() . '/' . $this->id;
    }

    /**
     * Runs this action with the specified parameters.  使用这些参数运行该方法
     * This method is mainly invoked by the controller.  这种方法主要是由控制器调用。
     *
     * @param array $params the parameters to be bound to the action's run() method.  参数将会绑定在 action 的 run() 方法上
     * @return mixed the result of the action  该 action 的返回结果
     * @throws InvalidConfigException if the action class does not have a run() method  如果该 action 没有 run() 方法将会跑出 InvalidConfigException 异常
     */
    public function runWithParams($params)
    {
        if (!method_exists($this, 'run')) {
            throw new InvalidConfigException(get_class($this) . ' must define a "run()" method.');
        }
        $args = $this->controller->bindActionParams($this, $params);
        Yii::trace('Running action: ' . get_class($this) . '::run()', __METHOD__);
        if (Yii::$app->requestedParams === null) {
            Yii::$app->requestedParams = $args;
        }
        if ($this->beforeRun()) {
            $result = call_user_func_array([$this, 'run'], $args);
            $this->afterRun();

            return $result;
        } else {
            return null;
        }
    }

    /**
     * This method is called right before `run()` is executed.  该方法将会在 `run()` 方法调用之前被执行
     * You may override this method to do preparation work for the action run.  你可以重写该方法为 action 做准备工作
     * If the method returns false, it will cancel the action.  如果该方法返回 false ， action 将不会执行
     *
     * @return boolean whether to run the action.  是否执行 action
     */
    protected function beforeRun()
    {
        return true;
    }

    /**
     * This method is called right after `run()` is executed.  该方法将会在 `run()` 方法调用之后被执行
     * You may override this method to do post-processing work for the action run.  你可以重写该方法来为 action 的运行后处理
     */
    protected function afterRun()
    {
    }
}
