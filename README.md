#csss
v0.1
 
===

A css booster on php server to Compressed css and merged into one, reduce the number of HTTP requests to speed up the web loading speed

一个用于PHP服务器压缩合并多个css文件的工具，能够在第一次请求时自动合并所需css文件并缓存起来，大大减少了http请求时间和传输流量。也免去了每次发版手动压缩的烦恼

新增加了[SAE](http://sae.sina.com.cn/)支持

#Usage
-------
>Before if you want 2 inlcude 1.css and 2.css to your web page,u may inclue css files like this
    
    <script src="./1.css"></script>    
    <script src="./2.css"></script>
    
>Now you can do just like this!

    <script src="./css.php?files=1.css,2.css"></script>
    
wow so easy


[@粥米鱼](http://weibo.com/bcker)
