# React Admin

Whoa! Look out! *Bonus* chapter time! We *know* that our API is fully described using
the Open API spec. We can actually see this by going to `/api/docs.json`. This shows
all of our different endpoints and the fields on them. It gets this delicious info
by reading our *code*, PHPdocs, and other things. And we know this is used to power
the Swagger UI admin page. Our API is *also* equally described by JSON-LD Hydra.

Both of these types of API docs can be used to power *other* things.

For example, if you search for "react admin", you'll find an open source React-based
admin system. This is *incredibly* powerful and it's been around for a long time.
And the way it works is really cool: we point React at our API documentation and
then... just builds itself! Let's take it for a test drive.

Search for "api platform react admin" to find the API Platform docs page all about
this. This has some info... but what we're really after is over here. Click "Get
Started". This walks through all the details you'll need, even including CORS
config if you have that problem.

So... let's do it!

## Webpack Encore Setup

If you use the API Platform Docker distribution, this admin area comes pre-installed.
But it's pretty easy to add it manually. Right now, our app doesn't have *any*
JavaScript, so we need to bootstrap that. Find your terminal and run:

```terminal
composer require encore
```

This installs WebpackEncoreBundle... and its recipe gives us a basic frontend
setup. When that's done, install the Node assets with

```terminal
npm install
```

Okay, flip back over the documentation. API Platform has their own Node package
that helps integrate with the admin, so let's get that installed. Copy the
`npm install` line  -you can also use `yarn` if you want -, paste it in the terminal,
and I'll also add a `-D` at the end.

```terminal-silent
npm install @api-platform/admin -D
```

## UX React Setup

That's not super important, but I tend to install my assets as `devDependencies`.

To get all of this working, ultimately, we're going to render a single React
component into our page. To help with that, I'm going to install a UX package that's...
just really good at rendering React components. It's optional, but nice.

Run:

```terminal
composer require symfony/ux-react
```

*Perfect*. Now, spin over and search for "symfony ux react" to find its documentation.
Copy this setup code: we need to add it to our `app.js` file... over here in
`assets/`. Paste... and we don't need all of these comments. I'll also move this
code down below all of the imports.

Awesome! This basically says that it will look in an `assets/react/controllers/`
directory and make every React component there super easy to render in Twig.
So, let's create that: inside of `assets/`, add two new directories:
`react/controllers/`. And inside of *that*, we'll create a new file called
`ReactAdmin.jsx`.

For the *contents*, go back to the API Platform docs.. and it gives us *almost*
exactly what we need. Copy that and paste it inside our new file. But first, it
doesn't *look* like it, but thanks to the JSX, we're using React, so we need an
`import React from 'react'`.

Let's make sure we have that installed:

```terminal
npm install react -D
```

## Passing a Prop to the React Component

Second, take a look at the `entrypoint` prop. This is *so* cool. We pass the URL
to our API homepage... and the React admin takes care of the rest. For us, this
URL would be something like `https://localhost:8000/api`. But... I'd rather not
have to hardcode a "localhost" URL into my JavaScript.

*Instead*, we're going to allow this to be passed in as a prop. To do that, add
a `props` argument... then say `prop.entrypoint`.

How do we pass this in? We'll see that in *just* a minute.

## Enabling React in Encore

All right, let's see if we can get our system to build. Run:

```terminal
npm run watch
```

And... *syntax error*! It sees this `.jsx` syntax and... has no idea what to do
with it! That's because we haven't enabled React inside of WebpackEncore yet. Hit
Ctrl+C to stop that.. then spin over and open `webpack.config js`. Find a comment
that says `.enableReactPreset()`. There it is. Uncomment that.

Now when we run

```terminal
npm run watch
```

again... it *still* won't work! *But* it gives us a command that we can run
to install the missing package we need for React support! Copy that, run it:

```terminal-silent
npm install @babel/react-preset@^7.0.0 --save-dev
```

And *now* when we run

```terminal
npm run watch
```

... it works! This means we have a new React component called `ReactAdmin`. Though...
we're not rendering it anywhere yet.

## Rendering the ReactAdmin Component

How do we do that? This is the easy part. In `src/Controller/`, create a new PHP
class called `AdminController`. This is probably going to be the most *boring*
controller you've ever seen. Make it extend `AbstractController`, and create
a `public function` called `dashboard()`, which will return a `Response`, even though
that's optional. Above this, add a `Route()` for `/admin`.

Pretty easy, right? And all we need inside is `return $this->render()` and then
a template: `admin/dashboard.html.twig`.

Cool! Down in the `templates/` directory, create that `admin/` directory... and
inside a new file called `dashboard.html.twig`. Again, this is probably one of the
most *boring* templates you'll ever make, at least at the start. Extend
`base.html.twig` and add the `block body` and `endblock`.

Now, how do we render the React component? Thanks to that UX React package, it's
*super* easy. Create an element that it should render into then add
`react_component()` followed by the name of the component. Since the file is called
`ReactAdmin.jsx` in this directory, its name will be `ReactAdmin`.

And here's where we pass in those props. Remember we have one called `entrypoint`.
Oh, but let me fix my indentation... and remember to add the `</div>`. We don't
need anything *inside* the div, because that's where the React admin area will
render.

Anyways, pass the `entrypoint` prop set to the normal `path()` function. Now we
just need to find the route name that API Platform uses for the API homepage. This
tab is running npm... so I'll open a new terminal tab and run:

```terminal
php bin/console debug:router
```

Woh! Too big. That's better. Scroll up a bit, and... here it is. We want this:
`api_entrypoint`. Head back over and pass that in.

Phew! Moment of truth! Find your terminal, change the address to `/admin`, and...
*hello* ReactAdmin! Woh! Behind the scenes, that made a request to our API entrypoint,
saw all of the different API resources we have, and it created this admin! I know,
isn't that insane?

We won't go *too* deep into this, though you *can* customize it and you almost
definitely *will* need to customize it. But we get a lot of stuff out of the box.
It's not *perfect*, it looks a a little confused by our embedded `DragonTreasures`,
but it's already *very* powerful. Even the validation works! Watch: when I submit,
it reads the server-side validation that our API returned and assigned each error
to the correct field. And treasures is aware of our filters... it's *all there*.

If this is interesting to you, *definitely* check it out further.

All right, team! You did it! You got through the first API Platform tutorial, which
is fundamental to *everything*. You now understand how resources are serialized,
how resources relate to other resources, IRIs, etc. All of these things are going
to empower you no matter what API you're building. In the next tutorial, we'll talk
about users, security, custom validation, user-specific fields and other wild stuff.
Let us know what you're building and if you have any questions, we're here for you
down in the comments section.

Alright, friends! Seeya next time!
