jqBus
=====

The jqBus is based on jQuery ajax capabilities and a server side handler.
The jqBus is working with an "envelope" object and returning a unified response object.
The envelope structure example:
var envelope = {service: 'MyService', method: 'doSomething', callback: myCallback, data: [param1,param2]}
service - server side class name
method - a method inside the server side class
callback - javascript function to call when the operation is completed (success and failure) with the bus response object
data - (optional) - array of parameters to send to the class method

Bus response object:
{result: 'success|failure', data: {}}
result - consist of the text success or failure which indicate the operation result
data - mixed. can be any data type which was serialized by the server side bus handler.
       In case of an failure result, the data might have an error description, if available


Requirements
------------
* jQuery 1.2.6 or newer

* PHP 5.2.6 or newer

* Having php classes in one place (set the location in bus.php) with template naming convention:
  classname.class.php, all lower case and the classname should match exactly the class name.
  For example, class named User should be saved as user.class.php

* The PHP class should expose the services as public functions. For security reasons any function
  which should not be exposed to the client should be set as private or protected.

* In IE the JSON library (https://github.com/douglascrockford/JSON-js)


Author
------
Itzik Paz
https://github.com/spideron

Copyright and license
---------------------
Modular Patterns Ltd. (c) 2012 All Rights Reserved

Licensed under the Apache License, Version 2.0 (the "License"); you may not use this work except in compliance with the License. You may obtain a copy of the License at:

http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software distributed under the License is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the License for the specific language governing permissions and limitations under the License.