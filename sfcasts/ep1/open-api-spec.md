# The Powerful OpenAPI Spec

I said that these interactive docs come from an open source library called Swagger
ui, and as long as your API contains some configuration that describes the api, like
what endpoints it has and what fields are used on each endpoint, then you can
generate these rich swagger docs automatically. Head two, pet store three swagger.io.
This is really cool. This is a demo project of another of the Swagger UI being used
on another api, on a demo api, and what's really cool is that this actually sh has a
link to the configuration file that's behind this Swagger ui. In fact, you can put
any uh, swagger configuration up here and it'll load, so we'll open that up and
awesome. This Jsun file fully describes the API from some basic information about the
API itself, all the way down to the different paths that you have updating an
existing pet, for example, adding a new pet to the store, the responses, everything
as long as your API contains, if you have one of these files, you can get swagger
instantly. This format is called Open api. Open API is just a standard for how APIs
should be, should be described. It's literally this file right here,

So back over in our docs, we must have that same configuration file, right? We do
head to /api /docs.JSON two. See our version of that file. It looks very similar.
It's got paths, it's describing the different operations. This is really cool. API
platform is reading our project and generating this giant file for us. Then because
we have this giant file, it can generate this swagger ui. In fact, the way it does
that, if you view the page source on this page is the actual that JSON document we
saw is actually embedded right into the HTML of the page. Then there's some swagger
JavaScript that reads this and boots up this page. Now, this idea of having an op
open API specification that describes your API is super powerful because there are
increasing tools that you can build on top of this kind of like swagger ui for
example. Go back to the API platform documentations and click on Schema generator.
This is actually a tool written by API platform where if you want to, you can
actually generate, you can use a service called Stoplight.

No, no, no.

You can use a service called Stoplight. This is a screenshot down here to actually
design your API first. It will then give you an open API specification document, and
then you can use a tool called the Schema generator to generate your PHP classes.
From that. It's not something we're going to do in this tutorial, but it is one of
the options. There's also an admin generator. This is really cool. This is something
called React Admin, and basically you point React admin at your, or it generates from
this, and then you get an instant admin from that. And there's also another way to
create a, there's also other ways to generate code for your front end. For example,
you can generate next JS front end, which reads from your open API specification
anyways, having maybe you don't care about any of that point is you get this really
nice swagger ui, but the Open API spec behind this can be used in lots of other
powerful ways. Now, in addition to the endpoints down here, you can also see them
called schema. These are actually basically your models and there's a, there's two
right here, one for JSON LD and one normal one. We're going to talk about JSON LD in
a second, but these are basically the same

And E, and if you open up these things, it's actually really already really smart. It
knows our ID as an integer. It knows our name as a string. It knows our cool factors
as an integer, it knows our is published as a boo. All that information, again, is
coming from this spec document. If I search for is published in here, you can see,
yep, there is the model describing it is published as a Ty Bull APAP platform is
reading all this from our property types. So it's very simply it sees that cool
factor as an integer, and so it advertises it as an integer, but it's even cooler
than that. Check out the id. It's set as read only, huh? It knows that because ID is
a private property and there is no set ID method, so it infers that Id must be a read
only property and it's totally correct. Yep. APAP platform reads a lot about lot of
information from your code to generate really rich documentation automatically, and
you can even add more stuff to this. So let's find the value property there it is,
and I'm going to add a little documentation above this

So people know that this is the estimated value of this treasure in gold coins. When
you head over in refresh and check out your model down here for value, it shows up.
So if you just do a really good job writing your PHP code and documenting it, you're
going to kind of get a rich API out of the box. The same information is advertised.
All right, let's try our get endpoint again. Next, let's talk about the, because
next, let's talk about these weird AT fields, like at id at type and at context.
These come from something called jsun ld, a powerful addition to J S O, and though
it's technically optional, it's super important part of a API platform.
