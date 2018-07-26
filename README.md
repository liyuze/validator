# 简介
PHP 语言的参数验证器和挂载器。

# 安装

```json
//file:compose.json
{
    "require": {
        "liyuze/validator": "dev-master"
    }
}
```
```bash
compose update
```



或

```bash
compose install liyuze/validator dev-master
```

# 示例

### 参数集合对象初始化

1. 单一指定

   ```php
   function func ($name, $age) {
       $Parameters = new liyuze\validator\Parameters\Parameters([
           'name' => $name,
           'age'> $age,
       ]);
   }
   ```

2. 数组指定

   ```php
   $Parameters = new liyuze\validator\Parameters\Parameters($_POST);
   ```

   
###验证器

#### 验证规则设置

1. 设置验证规则

   ```php
   $Parameters->setRule([
       'name' => 'required|string|maxLength=15|minLength=5',
       'age' => 'integer|max=120|min=1'
   ]);
   ```

   

2. 参数和验证规则一同设置

   ```php
   $name = $_POST['name'];
   $age = $_POST['age'];
   
   $Parameters = new liyuze\validator\Parameters\Parameters();
   $Parameters->config([
       'name' => [$name, ['required', 'string', 'maxLength' => 15, 'minLength' => 5]],
       'age' => [$age, ['integer', 'max' => 120, 'min' => 1]]
   ]);
   ```



#### 验证和验证消息

```php
if ($Parameters->validate()) {
    //所有验证消息
    $Parameters->getErrors();
}
```



#### 验证消息





#### 挂载器

#### 新增自定义挂载器

```php
/**
 * 由年龄获取生肖、出生年份
 */
class AgeMounter extends liyuze\validator\Mounter\Mounter {
    
    public function registerKeys()
    {
        //挂载值的变量名
        return ['year', 'zodiac']
    }

    public function run()
    {
        //参数值
        $value = $this->getParameter()->getValue();
        //计算值
        $year = 2018;
        $zodiac = 'dog';
        //挂载值
        return [
            'year' => $year,
            'zodiac' => $zodiac
        ]
    }
}
```



#### 挂载器配置

```php
$Parameters->addMounter('age', 'mounter\AgeMounter')
```

#### 获取挂载值

```php
//获取出生年份
$year = $Parameters->getMounteValue('age', 'year');

//获取属相
$zodiac = $Parameters->getParam('age')->getMounteValue('zodiac');
```

