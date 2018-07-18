<?php

class Ioc
{
    // 获得类的对象实例
    public static function getInstance($className)
    {
        $paramArr = self::getMethodParams($className);
        return (new ReflectionClass($className))->newInstanceArgs($paramArr);
    }

    /**
     * 执行类的方法
     * @param  [type] $className  [类名]
     * @param  [type] $methodName [方法名称]
     * @param  [type] $params     [额外的参数]
     * @return [type]             [description]
     */
    public static function make($className, $methodName, $params = [])
    {
        // 获取类的实例
        $instance = self::getInstance($className);
        //var_dump($instance);
        // 获取该方法所需要依赖注入的参数
        $paramArr = self::getMethodParams($className, $methodName);
        //var_dump($paramArr);
        //var_dump(array_merge($paramArr, $params));
        //exit;
        return $instance->{$methodName}(...array_merge($paramArr, $params));
    }

    /**
     * 获得类的方法参数，只获得有类型的参数
     * @param  [type] $className   [description]
     * @param  [type] $methodsName [description]
     * @return [type]              [description]
     */
    protected static function getMethodParams($className, $methodsName = '__construct')
    {
        extract($_GET);
        $paramArr = []; //存储类方法的：参数、参数类型
        $class = new ReflectionClass($className);//通过反射获取指定类

        if ($class->hasMethod($methodsName)) {//判断指定类的指定方法是否存在
            $method = $class->getMethod($methodsName);//获取该类的指定方法
            $params = $method->getParameters();//获取该类指定方法的参数名列表(此处不会返回参数对应的参数类型)
            if (count($params) > 0) {
                foreach ($params as $key => $param) {
                    //echo '获取类型：'.$param->getType().'<hr />';
                    if ($paramClass = $param->getClass()) {//如果当前参数是class类型
                        $paramClassName = $paramClass->getName();//获取当前class类型参数的类名
                        $args = self::getMethodParams($paramClassName);
                        $paramArr[] = (new ReflectionClass($paramClassName))->newInstanceArgs($args);//创建一个类的新实例，给出的参数将传递到类的构造函数
                    } else {//如果当前参数不是class类型
                        $c_var = $param->getName();
                        $paramArr[] = $$c_var;
                    }

                }
            }
        }
        return $paramArr;
    }
}

class A
{
    public function __construct()
    {
        echo 'A->__construct()<br />';
    }

    public function hello()
    {
        echo 'A->hello()<br />';
    }
}

class B
{
    public function hello(A $a, $c = '', $d = '')
    {
        echo '参数c：' . $c . '<br />参数d：' . $d . '<br />';
        $a->hello();
    }
}

//demo.php?d=dval&c=cval
Ioc::make('B', 'hello');
