# Overview

[ProcessWire](https://processwire.com) is a very flexible content management framework and CMS. In addition to building websites, it can be used as a headless CMS. My usual workflow is to use ProcessWire as a data backend, exposing JSON endpoints that are ready to be consumed by a Vue.js front-end application.

The problem is that there's no unified way to define these endpoints. One approach is to create a template for each endpoint type (such as `User`), create a controller file for each template, juggle URL segments, validate request methods, and so on. This leads to quite a lot of boilerplate code. The page hierarchy does not always reflect the endpoint structure, and things can get messy quickly.

Luckily, ProcessWire introduced [URL hooks](https://processwire.com/blog/posts/pw-3.0.173/) in version `3.0.173`. This enables a different approach for defining the API, completely decoupling the page tree from the endpoint hierarchy and keeping the page tree free of actual endpoints. With proper base path configuration, your JSON endpoints can coexist with the website itself.

Since we're using ProcessWire pages as the main data source, the library includes a versatile [Page parser](/processwire-page-parser) tool to convert entire page trees into structured data, ready to be served as an API response.
