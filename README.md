<p style="text-align: center;">
  <img src="https://raw.github.com/cillosis/iceberg/master/Public/assets/img/iceberg.png"><br>
  <span style="font-size: 50px; vertical-align: top;">Iceberg PHP Framework</span>
</p>

<br>

What is Iceberg?
----------------
* * * 

Iceberg is a lightweight PHP framework for rapid development of small to mid-size projects. It leans more towards a *convention over configuration* design and currently only supports MySQL database interfaces. 

Why Another Framework?
----------------------
* * *

Many of the frameworks I have used are either extremely full featured and designed for *almost* every possible use case, or blindingly simple and designed to get out of your way (which isn't always a bad thing). I created Iceberg as a middle ground that provides just enough features to make my life easier, but not too many that small to mid-size projects don't suffer from complexity and bloat. This framework is not designed to be for **everyone** or **everything**. If this project fits your needs, great! If not, there are plenty of fantastic alternatives.

Getting Started
---------------
* * *

**Clone the project:** 
 ```git clone https://github.com/cillosis/iceberg.git ./myapp```

Once cloned, your file structure in *myapp* will look like:

    myapp  
      |
       --> App
      |     |
      |      -->Configs
      |      -->Layouts
      |      -->Logs
      |      -->Models
      |      -->Modules
      |      -->Plugins
      |      App.php
      |
       --> Lib
      |     |
      |      --> *
      |
       --> Public
      |     |
      |      --> assets
      |     |      |
      |     |       --> css
      |     |       --> font
      |     |       --> icon
      |     |       --> img
      |     |       --> js
      |     |       --> less
      |     .htaccess
      |     index.php

There are a few things you can configure:

- In App/Configs is a file called **Database.ini** which contains credentials for MySQL database connections. You will want to update these if using a database.

The Basics
----------
* * *

Iceberg follows an MVC pattern and routing follows the *Module/Controller/Action* pattern. By default, **Site** is the primary module, but you can create additional modules for things like admin areas, an API, etc. As I said before, Iceberg prefers a convention over configuration mentality which means a few things for you:

- A module, by default, utilizes a layout contained in *App\Layouts* of the same name. So if your module is called **Site**, then your layout will be called **site.phtml**. If that layout does not exist, the framework falls back and looks for a layout called **default.phtml**.
- The default entry point into a module is a controller called **Index** and a method in the controller class called `index()`.
- The controllers in your module know that a matching View should exist in the module and will render it automatically for you. So, if for example we have the **Index** controller with an `index()` action, in your module you should have a view at *App/Modules/Site/Views/Index/Index.phtml* where **Site** is the name of the module, **Index* is the name of the controller, and **Index.phtml** represents the action.

The App Class
-------------
* * *

While the entry point to the application starts in *Public/index.php*, that instantiates the **App** class and calls the `run()` method. This method initializes the application. The primary area of importance here is this is where you setup your routes.

Example:

`$router->addRoute('about', array('module' => 'site', 'controller' => 'index', 'action' => 'about'));`

This takes the **Router** library component and passes two parameters, the URL path and an array containing what module/controller/action to call if matched. We will discuss the Router component in more detail later.

**Static App Methods:**

`getUrl()` - *Retrieves the applications URL with protocol. Ex. http://www.myapp.com*<br> 
`getModel( $modelName )` - *Retrieves instance of a Model*<br>
`getComponent( $componentName )` - *Retrieves instance of library component such as "Router"*<br>
`setComponent( $componentName, $component )` - *Set or update an instance of a component*<br>
`getBasePath()` - Retrieves absolute path to application root.<br>
`getApplicationPath()` - Retrieves absolute path to App folder.<br>
`getLibraryPath()` - Retrieves absolute path to Lib folder.<br>
`getPublicPath()` - Retrieves absolute path to Public folder.<br>

These methods are available at any point in the application using the global `Iceberg` namespace object. For example, if inside a controller and you need a model, you can grab it like this:

    $userModel = Iceberg::getModel('User');

Controllers
-----------
* * *

Coming soon.

Views
-----------
* * *

Coming soon.

Models
-----------
* * *

Coming soon.

