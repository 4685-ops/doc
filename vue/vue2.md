## **插值表达式**

​	**{{表达式}}**

​	**响应式特性**

​	**数据的响应式处理->数据变化视图自动更新**

## **指令**

### 	**v-html：设置元素的innerHtml**

### 	**v-show:控制元素的显示隐藏**

​			**1.true:显示 false:隐藏**

​			**2.原理切换css 	display:none**

### v-if:控制元素的显示隐藏（条件渲染）

​		**1.true:显示 false:隐藏**

​		**2.判断条件 控制元素的创建和移除**

​		**3.v-else  v-else-if**

​			**3.1:例子**

  			***v-if="generate==1">***
  			***v-else***

​		**3.2:例子**

		<p v-if="score>=90">A</p>
		<p v-else-if="score>=80">B</p>
		<p  v-else>C</p>


### **v-on** 

​	**注册事件=添加监听+提供处理逻辑** **v-on: 简写为@**	

​	**v-on:事件名="内联语句"**

​		v-on:click="count++" -> @click="count++"

​	**v-on:事件名="methods中的函数名"**

​	**v-on:调用传参**

​		**@click="fn(参数1，参数2)>**

### **v-bind**

​	**动态的设置html的标签属性 -> src  url  title....**

​	**v-bind:属性名="表达式"**

​	**v-bind:src="imgUrl" ->** **:src="imgUrl"**

### **v-for**

	<p v-for="(item,index) in 数组">我是内容</p>

​	**删除的方法** **filter:根据条件，保留满足条件的事件 得到一个新的数组**

​	**this.booksList =** **this.booksList.filter(item => item.id!==id)**

​	**:key 给元素增加了唯一标识**

### **v-model** 

​	**给表单元素使用 双向绑定  可以快速的获取或设置表单内容**

```
<input type='text' v-model="username" />
new Vue({
    el:"#app",
    data:{
        username:''
    }
})
```

往数组添加数据方法

```
this.list.unshift({
    id: id,
    name:this.username
})
```

## **指令修饰符**

​	@keyup.enter 键盘回车监听

​	v-model.trim   去除首尾空格

​	v-model.number   转数字

​	@事件名.stop 阻止冒泡

​	@事件名.prevent 阻止默认行为