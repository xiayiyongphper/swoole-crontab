## 目录说明
这里存放的是MQ消息事件的处理类。
MQProcess获取消息通过socket转发过来统一由MQProcessor处理，根据KEY路由到相应的类处理。

原因：可以动态reload。

## KEY与目录说明
先根据功能分，然后根据版本分。版本命名不要用“.”，用“_”代替。
如：版本v1.0文件夹要命名成v1_0。
可以查看MQProcessor::getResource()方法查看源码。