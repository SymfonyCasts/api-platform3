# The Powerful OpenAPI Spec

Earlier, I said that these interactive docs come from an open source library called
Swagger UI. And as long as you have some config that *describes* your
API, like what endpoints it has and what fields are used on each endpoint, then you
can generate these rich Swagger docs *automatically*.

Head to https://petstore3.swagger.io. This is really cool: it's is a demo project
where Swagger UI is being used on a demo API. And, it has a link to the API
configuration file that's powering this!

## Hello OpenAPI!

Let's... see what that looks like! Woh! Yea, this JSON file fully describes the API,
from basic information about the API itself, all the way down to the different
URLs, like updating an existing pet, adding a new pet to the store, the responses...
*everything*. *If* you have one of these files, then you can get Swagger
*instantly*.

The format of this file is called *OpenAPI*, which is just a standard for *how*
APIs should be described.

***TIP
In newer projects, access the JSON docs at `/api/docs.jsonopenapi`.
***

Back over in our docs, we *must* have that same type of config file, right? We do!
Head to `/api/docs.json` to see *our* version. Yup! It looks *very* similar.
It has paths, it describes the different operations... everything. The *best* part
is that API Platform reads our code and generates this giant file *for* us. Then,
*because* we have this giant file, we get Swagger UI.

In fact, if you click on "View Page Source", you can see that this page works by
embedding the actual JSON document right into the HTML. Then, there's some Swagger
JavaScript that reads that and boots things up.

## OpenAPI & Free Tools

This idea of having an OpenAPI specification that describes your API is
powerful... because there are an increasing number of tools that can *use* it.
For example, go back to the API Platform documentation and click on "Schema
Generator". This is pretty wild: you can use a service called "Stoplight" to
*design* your API. That will give you an OpenAPI specification document... and then
you can use the Schema Generator to generate your PHP classes *from* that. We're
not going to use that, but it's a cool idea.

There's also an admin generator built in React - we'll play with this later - and
even ways to help generate JavaScript that talks to your API. For example, you can
generate a Next.js frontend by having it read from your OpenAPI spec.

The *point* is, Swagger UI is awesome. But even *more* awesome is the OpenAPI
spec document behind this... which can be used for other stuff.

## Models / Schema in OpenAPI

In addition to the endpoints in Swagger, it also has something called "Schemas".
These are your models... and there are two - one for JSON-LD and a normal
one. We're going to talk about JSON-LD in a minute, but these are basically the same.

If you open one up, wow, this is smart. It knows that our `id` is an integer,
`name` is a string, `coolFactor` is an integer, and `isPublished` is a boolean.
All of this info is, once again, coming from this spec document. If we search
for `isPublished` in here... yep! *There's* the model describing `isPublished` as
`type` `boolean`. The best part is that API Platform is generating this by... just
looking at our code!

For example, it sees that `coolFactor` has an integer type:

[[[ code('b6ad349c37') ]]]

so it *advertises* it as an integer in OpenAPI. But it gets even *better*. 
Check out the `id`. It's set as `readOnly`. How does it know that? Well, `id` is a 
*private* property and there's *no* `setId()` method:

[[[ code('4b1daaab89') ]]]

And so, it correctly inferred that `id` must be `readOnly`.

We can also help API Platform. Find the `$value` property... there it is... and add
a little documentation above this so people know that `This is the estimated value
of this treasure, in gold coins.` 

[[[ code('1b433f924f') ]]]

Head over, refresh... and check out the model down here. For `value`... it shows up! 
The point is: if you do a good job writing your PHP code and documenting it, you're 
going to get rich API documentation thanks to OpenAPI, with zero extra work.

Next: Let's talk about these weird `@` fields, like `@id`, `@type`, and `@context`.
These come from something called JSON-LD: a powerful addition to JSON that API Platform
leverages.
