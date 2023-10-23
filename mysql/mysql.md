# sql执行过程

### 1.客户端 

##### 		第一步，navicat,mysql front,jdbc,SQLyog等非常丰富的客户端

### 2.连接器 

##### 		第二步，你会先连接到这个数据库上，这时候接待你的就是连接器。连接器负责跟客户端建立连接、获取权限、维持和管理连接。

### 3.查询缓存（mysql8.0已经移除）

##### 		连接器得工作完成后，客户端就可以向 MySQL 服务发送 SQL 语句了，MySQL 服务收到 SQL 语句后，就会解析出 SQL 语句的第一个字段，看看是什么类型的语句。

##### 		如果 SQL 是查询语句（select 语句），MySQL 就会先去查询缓存（ Query Cache ）里查找缓存数据，看看之前有没有执行过这一条命令，这个查询缓存是以 key-value 形式保存在内存中的，key 为 SQL 查询语句，value 为 SQL 语句查询的结果。

##### 		如果查询的语句命中查询缓存，那么就会直接返回 value 给客户端。如果查询的语句没有命中查询缓存中，那么就要往下继续执行，等执行完后，查询的结果就会被存入查询缓存中。

##### **查询缓存挺鸡肋的原因：**

##### 		对于更新比较频繁的表，查询缓存的命中率很低的，因为只要一个表有更新操作，那么这个表的查询缓存就会被清空。如果刚缓存了一个查询结果很大的数据，还没被使用的时候，刚好这个表有更新操作，查询缓冲就被清空了，相当于缓存了个寂寞。基本上除了一些字典相关的表，都会有更新操作，而字典相关的表一般数据量不大。（更新数据 缓存就会频繁的更新）

### 4.分析器（解析 SQL）

##### 	1.**词法分析**

###### 			MySQL 会根据你输入的字符串识别出关键字出来，构建出 SQL 语法树，这样方便后面模块获取 SQL 类型、表名、字段名、 where 条件等等。

##### 	2.**语法分析**

###### 			根据词法分析的结果，语法解析器会根据语法规则，判断你输入的这个 SQL 语句是否满足 MySQL 语法。

### 5.优化器

##### 		经过了分析器，MySQL 就知道你要做什么了。在开始执行之前，还要先经过优化器的处理。 优化器是在表里面有多个索引的时候，决定使用哪个索引；或者在一个语句有多表关联（join）的时候，决定各个表的连接 顺序。

### 6.执行器 

​		经历完优化器后，就确定了执行方案，接下来 MySQL 就真正开始执行语句了，这个工作是由「执行器」完成的。在执行的过程中，执行器就会和存储引擎交互了，交互是以记录为单位的。

以下三种方式执行过程，跟大家说一下执行器和存储引擎的交互过程。

- **主键索引查询**
- **全表扫描**
- **索引下推**

​		开始执行的时候，要先判断一下你对这个表 T 有没有执行查询的权限，如果没有，就会返回没有权限的错误，如下所示 (在工程实现上，如果命中查询缓存，会在查询缓存返回结果的时候，做权限验证。查询也会在优化器之前调用 precheck 验证权限)。

​		select * from test where id = 1;

​		如果有权限，就打开表继续执行。打开表的时候，执行器就会根据表的引擎定义，去使用这个引擎提供的接口。比如我们这个例子中的表 test 中，ID 字段没有索引，那么执行器的执行流程是这样的：

1. 调用 InnoDB 引擎接口取这个表的第一行，判断 ID 值是不是 10，如果不是则跳过，如果是则将这行存在结果集中；
2. 调用引擎接口取“下一行”，重复相同的判断逻辑，直到取到这个表的最后一行。
3. 执行器将上述遍历过程中所有满足条件的行组成的记录集作为结果集返回给客户端。

​		对于有索引的表，执行的逻辑也差不多。第一次调用的是“取满足条件的第一行”这个接口，之后循环取“满足条件的下一行”这个接口，这些接口都是引擎中已经定义好的。你会在数据库的慢查询日志中看到一个 **rows_examined** 的字段，表示这个语句执行过程中扫描了多少行。这个值就是在执行器每次调用引擎获取数据行的时候累加的。在有些场景下，执行器调用一次，在引擎内部则扫描了多行，因此引擎扫描行数跟 rows_examined 并不是完全相同的。

### 7.InnoDB  MyISAM  Archive

​	存储数据 提供接口

### 8.文件系统 （mac windows linux）

# mysql存储引擎特点



## **1. InnoDB**

### **(1). 介绍**

**InnoDB是一种兼顾高可靠性和高性能的通用存储引擎，在MySQL 5.5之后，InnoDB是默认的MySQL存储引擎。**

### **(2). 特点**

- **DML操作遵循ACID模型，支持事务；**
- **行级锁，提高并发访问性能；**
- **支持外键FOREIGN KEY约束，保证数据的完整性和正确性；**

### **(3). 文件**

**xxx.ibd：xxx代表的是表名，innoDB引擎的每张表都会对应这样一个表空间，存储该表的表结构（frm-早期的、sdi-新版的）、数据和索引。**

**参数：innodb_file_per_table**

```sql
show variables like 'innodb_file_per_table';
```

![img](https://pic1.zhimg.com/80/v2-9aaab20188fd46224f942d8fecceee9d_720w.webp?source=2c26e567)

**如果该参数开启，代表对于InnoDB引擎的表，每一张表都对应一个ibd文件。我们直接打开MySQL的数据存放目录，这个目录下有很多文件夹，不同的文件夹代表不同的数据库，我们直接打开[yun3k文件夹](https://www.zhihu.com/search?q=yun3k文件夹&search_source=Entity&hybrid_search_source=Entity&hybrid_search_extra={"sourceType"%3A"answer"%2C"sourceId"%3A3104482752})。**

![img](https://pic1.zhimg.com/80/v2-a94e50f591685791f94628f90dcde1cf_720w.webp?source=2c26e567)

**可以看到里面有很多的idb文件，每一个ibd文件就对应一张表，比如：我们有一张表account，就会有这样一个account.ibd文件，而在这个[ibd文件](https://www.zhihu.com/search?q=ibd文件&search_source=Entity&hybrid_search_source=Entity&hybrid_search_extra={"sourceType"%3A"answer"%2C"sourceId"%3A3104482752})中不仅存放表结构、数据、还会存放该表对应的索引信息。而该文件是基于[二进制](https://www.zhihu.com/search?q=二进制&search_source=Entity&hybrid_search_source=Entity&hybrid_search_extra={"sourceType"%3A"answer"%2C"sourceId"%3A3104482752})存储的，不能直接基于记事本打开，我们可以使用mysql提供的一个指令[ibd2sdi](https://www.zhihu.com/search?q=ibd2sdi&search_source=Entity&hybrid_search_source=Entity&hybrid_search_extra={"sourceType"%3A"answer"%2C"sourceId"%3A3104482752})，通过该指令就可以从ibd文件中提取sdi信息，而[sdi数据字典](https://www.zhihu.com/search?q=sdi数据字典&search_source=Entity&hybrid_search_source=Entity&hybrid_search_extra={"sourceType"%3A"answer"%2C"sourceId"%3A3104482752})信息中就包含该表的表结构。**

![img](https://pic1.zhimg.com/80/v2-86e34aa283c603645f8abc29a3968a65_720w.webp?source=2c26e567)

### **(4). 逻辑[存储结构](https://www.zhihu.com/search?q=存储结构&search_source=Entity&hybrid_search_source=Entity&hybrid_search_extra={"sourceType"%3A"answer"%2C"sourceId"%3A3104482752})**

![img](https://pica.zhimg.com/80/v2-4114ae16db94b406f961f9dd2f3d04be_720w.webp?source=2c26e567)

- **表空间：InnoDB存储引擎逻辑结构的最高层，ibd文件其实就是表空间文件，在表空间中可以包含多个[Segment段](https://www.zhihu.com/search?q=Segment段&search_source=Entity&hybrid_search_source=Entity&hybrid_search_extra={"sourceType"%3A"answer"%2C"sourceId"%3A3104482752})。**
- **段：表空间是由各个段组建的，常见的段有数据段、[索引段](https://www.zhihu.com/search?q=索引段&search_source=Entity&hybrid_search_source=Entity&hybrid_search_extra={"sourceType"%3A"answer"%2C"sourceId"%3A3104482752})、回滚段等。InnoDB中对于段的管理，都是引擎自身完成，不需要人为对其控制，一个段中包含多个区。**
- **区：区是表空间的单元结构，每个区的大小为1M。默认情况下，InnoDB存储引擎页大小为16k，即一个区中一共有64个连续的页。**
- **页：页是组成区的最小单元，页也是InnoDB存储引擎磁盘管理的最小单元，每个页的大小默认为16KB。为了保证页的连续性，InnoDB存储引擎每次从磁盘申请4-5个区。**
- **行：InnoDB存储引擎是[面向行](https://www.zhihu.com/search?q=面向行&search_source=Entity&hybrid_search_source=Entity&hybrid_search_extra={"sourceType"%3A"answer"%2C"sourceId"%3A3104482752})的，也就是说数据是按行进行存放的，在每一行中除了定义表时所指定的字段以外，还包含两个隐藏字段(后面会详细介绍)。**

## **2. MyISAM**

### **(1). 介绍**

**MyISAM是MySQL早期的默认存储引擎。**

### **(2). 特点**

**不支持事务，不支持外键**

**支持表锁，不支持行锁**

**访问速度快**

### **(3). 文件**

**xxx.sdi：存储表结构信息**

**xxx.MYD：存储数据**

**xxx.MYI：存储索引**

![img](https://picx.zhimg.com/80/v2-1fdb75a8ca1688c32c945c576b6c1bcf_720w.webp?source=2c26e567)

## 3. Memory

### **(1). 介绍**

**Memory引擎的表数据是存储在内存中的，由于受到硬件问题或断电问题的影响，只能将这些表作为[临时表](https://www.zhihu.com/search?q=临时表&search_source=Entity&hybrid_search_source=Entity&hybrid_search_extra={"sourceType"%3A"answer"%2C"sourceId"%3A3104482752})或缓存使用。**

## **(2). 特点**

**内存存放**

**[hash索引](https://www.zhihu.com/search?q=hash索引&search_source=Entity&hybrid_search_source=Entity&hybrid_search_extra={"sourceType"%3A"answer"%2C"sourceId"%3A3104482752})（默认）**

## **(3). 文件**

**xxx.sdi：存储表结构信息**

## **4. InnoDB、MyISAM和Memory的区别及特点**

| 特点                                                         | InnoDB             | MyISAM | Memory |
| ------------------------------------------------------------ | ------------------ | ------ | ------ |
| 存储限制                                                     | 64TB               | 有     | 有     |
| 事务安全                                                     | 支持               | -      | -      |
| 锁机制                                                       | 行锁               | 表锁   | 表锁   |
| B+tree索引                                                   | 支持               | 支持   | 支持   |
| Hash索引                                                     | -                  | -      | 支持   |
| [全文索引](https://www.zhihu.com/search?q=全文索引&search_source=Entity&hybrid_search_source=Entity&hybrid_search_extra={"sourceType"%3A"answer"%2C"sourceId"%3A3104482752}) | 支持（5.6版本之后) | 支持   | -      |
| 空间使用                                                     | 高                 | 低     | N/A    |
| 内存使用                                                     | 高                 | 低     | 中等   |
| 批量插入速度                                                 | 低                 | 高     | 高     |
| 支持外键                                                     | 支持               | -      |        |

## 四. 存储引擎的选择

**在选择存储引擎时，应该根据应用系统的特点选择合适的存储引擎。对于复杂的应用系统，还可以根据实际情况选择多种存储引擎进行结合。**

- **InnoDB：是Mysql的默认存储引擎，支持事务、外键。如果应用对事务的完整性有比较高的要求，在并发条件下要求数据的一致性，数据操作除了插入和查询之外，还包含很多的更新、删除操作，那么[InnoDB存储引擎](https://www.zhihu.com/search?q=InnoDB存储引擎&search_source=Entity&hybrid_search_source=Entity&hybrid_search_extra={"sourceType"%3A"answer"%2C"sourceId"%3A3104482752})是比较合适的选择。**
- **MyISAM：如果应用是以读操作和插入操作为主，只有很少的更新和删除操作，并且对事务的完整性、并发性要求不是很高，那么选择这个存储引擎是非常合适的。**
- **Memory：将所有数据保存在内存中，访问速度快，通常用于临时表及缓存。MEMORY的缺陷就是对表的大小有限制，太大的表无法缓存在内存中，而且无法保障数据的安全。**

![image-20231023194833722](C:\Users\leshu\AppData\Roaming\Typora\typora-user-images\image-20231023194833722.png)

![image-20231023194846145](C:\Users\leshu\AppData\Roaming\Typora\typora-user-images\image-20231023194846145.png)

![image-20231023194900707](C:\Users\leshu\AppData\Roaming\Typora\typora-user-images\image-20231023194900707.png)