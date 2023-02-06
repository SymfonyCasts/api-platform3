# React Admin

Coming soon...

Okay, bonus chapter time. Check this out. We know that our API is fully described
using the Open API spec. We can actually see this by going to slash api slash docs
dot J S O N. This shows all of our different endpoints, all the different fields on
them, and it's getting all this information by reading our code and also reading our
PHP documentation and other things. And we know that this is actually used to power
this Swagger UI page so that open API spec can be used for other stuff too. For
example, if you search for React admin, you'll find an open source library that is a
React based admin system and it's incredibly powerful and has been around for a long
time. The way this works is that you point this React component at your open API
documentation and then it just builds itself. So that's what I want us to do in this.
We're actually gonna try this. So I'm gonna search again for API platform React admin
to find their page all about this. And then I'll click down here for getting started.
Cause we'll kind of follow their guide a little bit. They show you exactly how to use
the Create RA React app, so you can create a totally standalone React application
that then boots up that admin area. So they have really good documentation on here,
including how to set up your cores headers in case your admin is on a different
domain than your api.

We're actually going to do this. We're gonna install straight into our Symphony
application using webpac Encore. So this is gonna be mostly just kind of setting up
Encore and React. So let's start by saying Composer require Encore to install Encore
bundle because we don't have that at all in our app right now. Perfect. And then
while that's done, we'll install the basic assets with NPM install Stop when that's
done. Flip back over the documentation. So API platform has their own node package
that helps integrate with the admin. So let's get that installed. I'll copy the NPM
install. You can also use yarn if you want. And I'll add a dash d doesn't really
matter, but I install my assets as Deb dependencies. And ultimately what we're gonna
have here is we're actually gonna create a React component. This thing here is a
React component and then we're gonna render it on a page in our site. So to render
React components, we don't have to do this, but we're gonna leverage a UX package to
make this even easier. So I'm gonna sell another package called say Composer, require
symphony slash ux React Perfect. And actually I'll spin over and also

Search for Simp UX React, see if we can find its documentation. Again, this is
optional. It's just a really small library that helps us, um, render React components
in our app. So I'm gonna copy this setup code we need to add here to our app JS file,
so assets and then app js. And then I'm gonna paste in that code we had earlier, but
we don't need all these comments. And I'm gonna move this down here to after my
stimulus bootstrap. Perfect. What this does is it says that it'll look in an assets
slash react slash controllers directory. In every React component we have inside of
there is something that we're gonna be able to render really easily in Twig. So
inside of assets, I'll create a new directory called React slash controllers.
Perfect. And inside there we'll create a new file called React admin dot jsx. And for
the contents of this, we'll go back to API platform and it gives us exactly what,
almost exactly what we need. So we'll paste that inside of there. There's just two
things that are different. One in this setup, because we are actually using React,
even though we don't see it here, we actually need to import React from React.

And because we're using React directly, I'm gonna make sure I have that installed.
MPM install React dash D. And then the other thing we want is notice this entry
point. This is really cool. So you start the admin and you point it basically at your
API homepage, and then it does the rest. So for us, this would basically be a local
host slash 8,000 slash api, but we don't wanna hard code that in there. So instead
what we're gonna do is we're gonna add props. Our props are gonna be passed here, and
we're gonna pass a crop called Entry Point. So we'll pass this Indy dynamically from
Twig. All right, so let's see if we can get our system to build. So let's run npm,
run watch, and air syntax. Air sees this jss syntax and it doesn't know what to do
with it. This is because I haven't actually enabled React inside of uh, Webpac Encore
yet. So let me close that up and then spin over and open webpac dot config js. And
inside of here, you should find a spot that says Enable React preset. Here it is.
This will enable react support.

And then when you run npm Run watch again, it still won't work, but it'll give you a
command that you can run to install the missing package that we need for React
support. Now when we run npm, run, run, watch again, it works. So what this means is
that we have a new React component called React Admin. Now we need to actually build
a page in Symphony that simply renders this. So this is the easy part. In source
controller, I'll create a new pH three class called about admin controller. This will
be probably the most boring controller you've ever seen, will make this extend
abstract controller. I'll create a public function call dashboard, make it return a
response even though that's not needed. And then above this we'll add a route for
slash admin. How about that? Easy. And all we need to do is just return this arrow
render and then some template. So admin slash dashboard html, that twig. Cool. Now
down in the templates directory, I'll create that a admin directory and a new file in
it called Dashboard html Twig. And again, probably one of the most boring templates
you'll ever have, at least to start, we'll extend the base template, block, body,

And block. And then for the actual internals of this, we're gonna render that React
component. And thanks to that UX React package, we can do that by just making any
element that it should render into then saying react underscore component, and then
the name of the component. So since we have react to Avon jsx in this directory, we
can call it react admin. And here is where we pass in those props. So remember we
have an entry point prop here. We're gonna pass in. That's supposed to point to our
homepage. So actually let me fix my indentation here and also put the closing diviv.
There we go. And we'll say entry point, and we're gonna point that at our route so we
can use the normal path function. And then we just need to go find the route name
that API platform uses for its homepage. So this is running npm. So I'm gonna open a
new terminal tab and run bin console, debug router, make that a little smaller,
scroll up a little bit and perfect. Here it is. This is what it wants. It wants the
API entry point. So we'll pass that in. All right, moment of truth, we're gonna head
back over here and change this to slash admin. And hello, react admin. So behind the
scenes that made a request or an entry point, it figured out all of the different API
resources that we have and it created this admin. We're not gonna go too deep into
this. This is highly customizable and you will customize it, but we get a lot of
stuff outta the box. Of course, it's not perfect. Looks a little confused by our
embedded dragon treasures,

But it's already very powerful. Even the validation works. So you see, when I save
there, it actually uses the server side validation on that and it signs it to the
correct field. Treasures is aware of our filters or with a filter on things,

It's all there. So this is cool. If this is interesting to you, check this out
further. Look into how to customize it. It's very, very powerful. All right, team,
you did it. You got through the first API platform tutorial, which is really
fundamental to everything. We understand deeply now about how our resources are
serialized, how resources relate to other resources, Iris, these are all the things
that are going to empower you no matter what API you're building. In the next
tutorial, we'll talk more about users and security. So check that out. All right,
friends. See you next time.

