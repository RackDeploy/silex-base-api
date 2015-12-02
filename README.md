The DialogTech omni-api project!
================================
This is an API framework built on Silex a micro-framework designed by the creators of Sympony2 inspired by Ruby Sinatra.
 
Objective
---------
To create a lightweight but fully featured API, intended as a single interface (internal or external) to both the East and Central platforms. This has been designed with the primary objective being to allow a developer to product quality "Clean Code" and re-inforce a "Good Developer Culture"

Development Environment
-----------------------
One of the requirements of 'Clean Code' is the ability for a developer to be able to spin up a production-like development environment, ensuring that the code which they develop will behave very similar to production. With that in mind, this repo utilizes docker to build the application container and vagrant to orchestrate on your local machine. Once you have checked out the repo, you can simply run the following to startup your evironment:

```console
vagrant up
```

While the scope of [docker](http://docker.io) and [vagrant](http://www.vagrantup.com) are outside of the bounds of this document, here are a few pointers:
