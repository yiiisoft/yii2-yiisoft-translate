<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\base;

/**
 * Arrayable is the interface that should be implemented by classes who want to support customizable representation of their instances.
 * 那些需要实现可定制表示的类需要实现 Arrayable 接口
 *
 * For example, if a class implements Arrayable, by calling [[toArray()]], an instance of this class
 * can be turned into an array (including all its embedded objects) which can then be further transformed easily
 * into other formats, such as JSON, XML.
 * 如果类实现了 Arrayable 接口，通过调用 [[toArray()]] ，该类的实例可以被转化为一个数组（包括所有它的嵌入类），可以进一步转化为其他格式，比如 Json XML
 *
 * The methods [[fields()]] and [[extraFields()]] allow the implementing classes to customize how and which of their data
 * should be formatted and put into the result of [[toArray()]].
 * 方法[[字段()]]和[[域外()]]允许实现类定制,哪些数据应该如何格式化并投入的结果[[toArray()]]。
 * [[fields()]] 和 [[extraFields()]] 方法允许实现类的定制，哪些数据需要格式化和放入 [[toArray()]] 的结果
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
interface Arrayable
{
    /**
     * Returns the list of fields that should be returned by default by [[toArray()]] when no specific fields are specified.
     * 返回字段的列表通过默认或当没有指定字段时调用 [[toArray()]]
     *
     * A field is a named element in the returned array by [[toArray()]].
     * 在 [[toArray()]] 返回的数组中一个字段命名为一个元素
     *
     * This method should return an array of field names or field definitions.  该方法返回一个字段名称和字段定义的数组
     * If the former, the field name will be treated as an object property name whose value will be used
     * as the field value. If the latter, the array key should be the field name while the array value should be
     * the corresponding field definition which can be either an object property name or a PHP callable
     * returning the corresponding field value. The signature of the callable should be:
     * 如果是前者，字段名称将被视为一个对象属性名称的值将被用作字段值。
     * 如果是后者，数组的键应该字段名,而数组值应该相应的字段定义可以是一个对象的属性名或一个PHP回调返回相应的字段值
     * 那回调的签名应该是：
     *
     * ```php
     * function ($model, $field) {
     *     // return field value
     * }
     * ```
     *
     * For example, the following code declares four fields:
     * 例如,下面的代码声明四个字段:
     *
     * - `email`: the field name is the same as the property name `email`;  字段名字是和属性名一样的
     * - `firstName` and `lastName`: the field names are `firstName` and `lastName`, and their
     *   values are obtained from the `first_name` and `last_name` properties;
     * 字段名称 `firstName` 和 `lastName` 从 `first_name` 和 `last_name` 属性中获取的
     * - `fullName`: the field name is `fullName`. Its value is obtained by concatenating `first_name`
     *   and `last_name`.
     *
     * ```php
     * return [
     *     'email',
     *     'firstName' => 'first_name',
     *     'lastName' => 'last_name',
     *     'fullName' => function ($model) {
     *         return $model->first_name . ' ' . $model->last_name;
     *     },
     * ];
     * ```
     *
     * @return array the list of field names or field definitions.
     * @see toArray()
     */
    public function fields();

    /**
     * Returns the list of additional fields that can be returned by [[toArray()]] in addition to those listed in [[fields()]].
     * 可以返回 [[toArray()]] 的字段除了 [[fields()]] 中的字段
     *
     * This method is similar to [[fields()]] except that the list of fields declared
     * by this method are not returned by default by [[toArray()]]. Only when a field in the list
     * is explicitly requested, will it be included in the result of [[toArray()]].
     * 这种方法和 [[fields()]] 相似，不会返回已经声明的字段。只有当一个字段列表中显式地请求,它将被包括在[[toArray()]]的结果。
     *
     * @return array the list of expandable field names or field definitions. Please refer
     * to [[fields()]] on the format of the return value.
     * @see toArray()
     * @see fields()
     */
    public function extraFields();

    /**
     * Converts the object into an array.  将对象转化成数组
     *
     * @param array $fields the fields that the output array should contain. Fields not specified
     * in [[fields()]] will be ignored. If this parameter is empty, all fields as specified in [[fields()]] will be returned.
     * 输出数组中应该包含的字段。没有被指定在 [[fields()]] 中的字段将会被忽略。如果该参数为空，所有指定的字段将在 [[fields()]] 中输出
     * @param array $expand the additional fields that the output array should contain.  输出数组中应该包含的附加字段
     * Fields not specified in [[extraFields()]] will be ignored. If this parameter is empty, no extra fields
     * will be returned.  没有在 [[extraFields()]] 中指定的字段将会被忽略。如果该参数为空，额外的字段将不会被输出。
     * @param boolean $recursive whether to recursively return array representation of embedded objects.
     * 是否递归的返回嵌入对象数组
     * @return array the array representation of the object  对象的数组表示
     */
    public function toArray(array $fields = [], array $expand = [], $recursive = true);
}
