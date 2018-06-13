# fastchat-se

## FastChat 简介
FastChat 是一款基于 Web 开发的即时通讯系统，它主打以下三个优势：
* 本项目拥有浏览器客户端，因此它是**跨平台、无需安装**的。
* 本项目前后端都使用docker来部署，致力于“一个命令即可使用”的**部署简便性**。
* 本项目是开源的，因此用户可以轻松地在自己的服务器上部署**私有、安全**的通讯服务。

## fastchat-se 项目介绍
`fastchat-se`就是`Fastchat`的一个服务端实现。`Fastchat`的Web客户端实现是`fastchat-fe`。

fastchat-se的大致工作流程如下：
1. 请求首先到达**反向代理服务容器(nginx)**。
2. 反向代理服务器首先将请求转发到**前端资源服务容器(nginx)**，这个nginx服务器对前端资源请求做出响应。
3. 如果没有匹配的前端资源，反向代理服务器将请求转发到**php服务容器(php-fpm)**。
4. 在php服务容器中，php-fpm启动一个php执行进程，执行**codeigniter**的代码，返回响应。
5. codeigniter可能需要数据库来进行持久化的存储，它由**数据库服务容器(mysql)** 来提供。

以上所有服务容器都已经配置为docker容器，都有各自的dockerfile用于构建image。两个`docker-compose.yml`(分别用于开发和生产)负责配置所有服务的运行。在服务器上通过`docker compose up`就能完成所有服务的部署。

## fastchat-se 预期API
fastchat-se提供RESTful API。

* 用户服务：
  * 用户注册。
  * 用户信息查询：提供一个userName或email，返回目标的用户信息(userName, nickname, email, gender)。
  * 用户信息修改：用户可以修改自己的除了userName以外的信息。
* 会话服务：
  * 身份认证：验证用户名和密码的正确性，返回会话凭据(json web token)。
  * 客户端发送请求时，要将会话凭据一并发送(除了注册、登陆这些不需要授权的请求)。服务端的会话服务需要验证会话凭据的正确性，并确认发送者的身份。
* 好友服务：
  * 指定userName，向其发送添加好友请求。
  * 获取发给自己的所有好友请求。为了减少请求次数，每个请求者的用户信息也一并返回。
  * 对一个发给自己的好友请求作出响应。
  * 获取自己的所有好友。返回每个好友的用户信息。
  * 查找与指定好友之间的**私聊**。根据userName返回chatId。
* chat服务：
  * chat(聊天)定义：一个chat由**参与者**和**消息历史**组成。
    * 参与者：chat的参与者是**两个**用户时，可以称为**私聊**；有**多个**用户时，可以称为**群聊**。
    * 消息历史：在chat中任何一个参与者发送的消息，都会进入**消息历史**。消息历史由chat拥有。chat的任何参与者都可以读取**消息历史**。每一个消息有一个messageId，messageId是auto increment的(便于比较消息新旧)。
  * 对于**群聊**和**私聊**的区别，提供以下额外说明：
    * 对于一个群聊，必须有一个参与者是master。
    * 群聊的参与者数量可以改变，私聊的不可以。
    * 每个chat的基本信息包括chatId, chatName, memberCount。群聊的chatName必须为string，私聊的chatName必须为null。
  * 获取参与的chat：查找自己参与的所有chat，返回每个chat的基本信息。另外，还要返回：该用户在每个chat上分别有多少条**未读消息**，服务器根据**lastReadMessageId**和**群聊当前的消息历史**来计算未读消息。
  * 获取chat的消息历史：根据chatId返回消息历史。
  * 获取chat的参与者：根据chatId返回参与者列表。返回每个参与者的用户信息。如果是群聊，还会返回master的userName。
  * 向指定的chat发送消息。
  * 提交lastReadMessageId：提交**当前用户**已经阅读的、最新的messageId。为每个用户都存储一个lastReadMessageId。
  * 搜索chat：根据chatId，返回chat的基本信息。
  * 另外，还有一些服务**只能用于群聊**：
    * 创建群聊：指定参与者(只能指定好友)，创建一个新的群聊。不需要指定自己，请求发送者必须参与。请求发送者是新建群聊的master。返回新建群聊的基本信息。
    * 退出群聊。master不可以退出群聊，必须先转移master身份。
    * 申请加入群聊：指定chatId，向其master发送加入申请。不能申请已经加入的群聊。
    * 只有master才有权限请求的服务：
      * 获取所有加入申请。
      * 对一个申请作出响应。
      * 修改chatName。
      * 增加、删除参与者。不可以删除master。
      * 删除一条或多条消息。
      * 转移master：指定另一个参与者为master，自己的master身份消失。