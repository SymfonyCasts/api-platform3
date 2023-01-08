# Swagger UI: Interactive Docs

The amazing interactive documentation that we've stumbled across is *not* something
from API platform! It's actually an open source API documentation library called
Swagger UI. And the *really* cool thing about Swagger UI is that if your API contains
metadata that *describes* your API, then *any* API can get this really cool,
interactive documentation for free! I love free stuff.

We get this because API platform *provides*  that metadata out of the box. But more
on that in a minute.

Let's play with this. Use the POST endpoint to create a new dragon treasure: we've
recently plundered some gold coins... which we got from Scrooge McDuck. None of the
other fields really matter. Down here, hit Execute and boom! Down here you can see
that this made a POST request to `/api/dragon_treasures` and sent all of that data as
JSON! Then, our API returned a 201 status code. A 201 status code means the request
was successful and resource was created. Then it returned this JSON... which includes
`id: 1`. Yes: this isn't just documentation. We really *do* already have a working
API!

There's also a couple of extra fields here that I mentioned earlier at contacts at ID
and at Type, and we'll talk about those soon. All right, so now that we have a dragon
treasure, let's open up this get end point here. Hit try it out, hit execute, and
perfect. This is super simple. In made a get request to /API /dragon_treasures. This
question Mark Page equal one is even optional and return this here you can see it's
inside something called Hydro ember. We'll talk about that later. But it's basically
just a list of the all of the dragon treasures we currently have, which is just this
one right here. So in just about just a couple minutes of work, we have a fully
featured API for our doctrine entity. I'm going to copy this. You all right here,
open a new tab and paste that in. Whoa, wait, this returned html, but a second ago it
said that it made a get request that you were out and it returned to .json. What's
going on here? Well, one of the features of API platform is it has is is called,
called Content Negotiation. It has the ability to return the same resource like our
Dragon treasure in multiple formats. Like for example, I could return it as .json or
it could return it as HTML

Or other formats. The way that you tell API platform what format you want is by
passing this accept header. So you can see when we use the interactive docs, it
passes this accept header it /you application /LD plus J S O. And even though we
don't see it here, if you go to a page in your browser like this, your browser
automatically sends an accept header that says you want text /html. So this is
actually API platform showing us what this resource looks like and it's HTML format.
And it's HTML format is just the documentation. In fact, as soon as I open up that
endpointed, automatically executed it for me. So this means if we want the J S O N
version, we need to pass this accept header,

Which you can easily do. For example, if you are writing JavaScript, but it is kind
of, it's not passing a custom accept header. Not really easy in a browser. And it
would be nice to see the jsun version of this. Fortunately, there's a platform gives
you a way to cheat. I'm actually going to take off this question Mark Page = one just
to simplify things. And on the end of any point you can say.json ld. You can say dot.
And then the format JSON LD is something we're going to talk about in a moment.
That's what gives us these extra this this specific format here. But now we see the
Dragon Treasure resource in the J S O LD format platform also supports JS O outta the
box. So same thing, but just in pure normal J S O N.

All

Right, so at this point we know we have a new route for /API and apparently we also
have a number of other routes like /API /dragon treasures and these, each of these is
a route, but where do those routes come from? How are those being dynamically added
to my app? Well, if you spin over to your terminal and re bin console debug router,
you'll see that all of these are just normal routes. Lemme make that a little bit
smaller. These are just normal traditional routes that are being added to our system.
How are these being added to our system? The answer to that is when we installed API
platform, its recipe added, a config routes API platform.yaml file.

This is actually a route import. I know it looks a little bit weird, but this
activates API platform while the routing is loading. And then API platform finds all
of the API resource things in our app, in our application and generates a route for
all of the endpoints. So what this means is all we need to focus on is creating these
beautiful PHP classes, decorating them with API resource. An a API platform takes
care of all the heavy lifting of hooking up those end points for us. Of course, we're
going to need to tweak configuration and talk about more advanced things, but hey,
that's the point of this tutorial. We're also off. We're already off to an awesome
start. So next I want to talk about the secret behind how this swagger UI gen
documentation is generated. It's called Open api.
