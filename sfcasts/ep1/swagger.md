# Swagger UI: Interactive Docs

The interactive documentation that you're looking at right now is actually not
something from API platform. This is an open source API documentation library called
Swagger ui. And the really cool thing about Swagger UI is that if your a p I contains
the right metadata that describes your A api, then any API can get this really cool
interactive documentation for free. And fortunately, API platform does provide all of
that metadata out of the box, but more on that in a few seconds. Let's play with this
a little bit. Let's use the post endpoint to create a new dragon treasure. Let's put
in some gold coins have we got from Scrooge McDuck and none of the other fields
really matter all that much. And then down here, hit execute and boom. Cool. Down
here you can see that it made a post request to /API /dragon treasures and then sent
all of that data as JS O. And what it returned was a 2 0 1 status code. A 2 0 1
status code means a resource on our a P I was created and then it returned this J S
O. Check it out ID one. Yes, it really did. Just insert that into the database. That
is awesome.

There's also a couple of extra fields here that I mentioned earlier at contacts at ID
and at Type, and we'll talk about those soon. All right, so now that we have a dragon
treasure, let's open up this get end point here. Hit try it out, hit execute and
perfect. This is super simple. In made a get request to /api /dragon_treasures. This
question Mark Page equal one is even optional and return this here you can see it's
inside something called hydro ember. We'll talk about that later. But it's basically
just a list of the all of the dragon treasures we currently have, which is just this
one right here. So in just about just a couple minutes of work, we have a fully
featured API for our doctrine entity. I'm going to copy this U URL right here, open a
new tab and paste that in. Whoa, wait, this returned html, but a second ago it said
that it made a get request that you were URL and it returned to J S O N. What's going
on here? Well, one of the features of API platform is it has is is called, called
content negotiation. It has the ability to return the same resource like our Dragon
treasure in multiple formats. Like for example, I could return it as J S O N or it
could return it as HTML

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
