# React Admin

Whoa! Look out! *Bonus* chapter time! We *know* that our API is fully described using the Open API spec. We can actually see this by going to `/api/docs.json`. This shows all of our different endpoints and the fields on them, and it's getting this information by reading our *code*, PHP *documentation*, and other things. And we know this is used to power this Swagger UI page so that Open API spec can be used for *other* stuff too.

For example, if you search for "react admin", you'll find an open source library that is a React-based admin system. This is incredibly powerful and it's been around for a long time. This works by pointing this React component at your Open API documentation and then it essentially just builds itself. That's what I want us to do with our app. So let's get started!

Search for "api platform react admin" to find their page all about this. This section gives you some handy information about the API Platform Admin, but what we're looking for is over here. Click on "Get Started". This is where they show you exactly how to use the Create React App to create a completely standalone React application that boots up that admin area. They have some *great* documentation here, including how to set up your CORS headers in case your admin is on a different domain than your API. This is what *we're* going to do. We're gonna install straight into our Symphony application using WebpackEncore, which mostly just involves setting up Encore and React.

Let's start over in our terminal with:

```terminal
composer require encore
```

This is going to install the Encore bundle, since we don't currently have that in our app. Perfect! When that's done, we'll install the basic assets with:

```terminal
npm install
```

Okay, now flip back over the documentation. API Platform has their own Node package that helps integrate with the admin, so let's get that installed next. I'll copy the `npm install` here (you can also use `yarn` if you want), paste it in the terminal, and I'll also add a `-D` at the end. This isn't super important, but I tend to install my assets as devDependencies. Ultimately, we're creating this React component, and then we're going to render it on a page in our site. We're actually going to leverage a UX package to make rendering this component even easier. This is *optional* but totally worth it. So let's install *another* package with:

```terminal
composer require symphony/ux-react
```

*Perfect*. I'll also spin over and search for "symfony ux react" to see if we can find its documentation as well. Again, this is *optional*. It's just a small library that helps us render React components in our app. I'll copy this setup code we need to add to our `app.js` file, which we'll find in `/assets`. I'll paste in the code... and we don't need all of these comments, so we can remove those. I'll also move this down here after the Stimulus `./bootstrap`. Awesome! This basically says that it will look in the `/assets/react/controllers` directory, and every React component we have inside of there is something that we'll be able to render *easily* in Twig. *So*, inside of `/assets`, I'll create a new directory called `react/controllers`. And inside *that*, we'll create a new file called `ReactAdmin.jsx`. For the *contents*, we'll go back to the API Platform docs and it gives us *almost* exactly what we need. I'll copy that and paste it inside our new file.

We *do* need to change a couple of things. First, since we're actually using React (even though we don't see it here), we need to `import React from 'react'`. And because we're using it directly, I'm going to make sure I have that installed by running:

```terminal
npm install react -D
```

Second, take a look at this `entrypoint`. This is really cool. This starts the admin, we're pointing it at our API homepage, and then it does the rest. For us, that would be something like `localhost:8000/api`, but we don't want to hard code that in there. *Instead*, we're going to add `props`. So right here, say `props` and we're going to pass a `prop` called `entrypoint`. We're passing this in *dynamically* from Twig.

All right, let's see if we can get our system to build. If we run

```terminal
npm run watch
```

and... *syntax error*. It sees this `.jsx` syntax and it doesn't know what to do with it. This is because I haven't actually enabled React inside of WebpackEncore yet. So let me close that... and then spin over and open `webpack.config js`. Here, you should find a spot that says `.enableReactPreset()`. There it is. This will enable React support. And when we run

```terminal
npm run watch
```

again... it *still* won't work. *But* it *will* give you a command that you can run to install the missing package we need for React support. Copy that, run it, and *now* when we run

```terminal
npm run watch
```

... it works! This means we have a new React component called `ReactAdmin.jsx`.

Now we need to actually *build* a page in Symphony that renders this. This is the easy part. In `/src/Controller`, let's create a new PHP 3 class called `AdminController`. This is probably going to be the most *boring* controller you've ever seen. We'll make this extend `AbstractController`, and create a `public function` called `dashboard()`, which will return a `Response`, even though that's not really needed. Above this, we'll add a `Route()` for `/admin`. Pretty easy, right? And all we need to do is just `return $this->render()` and then add our template - `admin/dashboard.html.twig`. Cool!

Down in the `/templates` directory, create that `/admin` directory... and inside that, add new file called `dashboard.html.twig`. Again, this is probably one of the most *boring* templates you'll ever have, at least at the start. We'll extend `base.html.twig` and add our `block body` and `endblock`. Then, for the actual internals of this, we're going to render that React component. Thanks to that UX React package, we can do that by just creating any element that it should render into, and then adding `react_component()` followed by the name of the component. Since we have `ReactAdmin.jsx` in this directory, we can call it `ReactAdmin`. And here's where we pass in those props. Remember that we have an `entrypoint` prop here we're going to pass in that will point to our homepage. I'll quickly fix my indentation here and add the `</div>`... there we go. Then we'll say `entrypoint`, and we're going to point that at our route, so we can use the normal `path()` function. Finally, we need to go find the route name that API Platform uses for its homepage. This is running NPM, so I'm going to open a new terminal tab and run:

```terminal
./bin/console debug:router
```

I'll make that a little smaller... scroll up a little bit, and... perfect! Here it is. We want this `api_entrypoint`, so head over and pass that in.

Okay, moment of truth! Head back over here, change this to `/admin`, and... *hello* ReactAdmin! Behind the scenes, that made a request to our entrypoint, saw all of the different API resources we have, and it created this admin. We won't go *too* deep into this. This is *highly* customizable and you *will* customize it, but we get a lot of stuff out of the box. It's not *perfect*, though, since it already looks a little confused by our embedded `DragonTreasures`, but it's already *very* powerful. Even the validation works! You can see that when I save this, it actually uses the server side validation and assigns it to the correct field. Treasures is aware of our filters... it's *all there*. Cool! If this is interesting to you, *definitely* check this out further. Look into how to customize it, because it's *very* powerful.

All right, team! You did it! You got through the first API Platform tutorial, which is fundamental to everything. We now understand how our resources are serialized, how resources relate to other resources, IRIs, etc. All of these things are going to empower you no matter what API you're building. In the next tutorial, we'll talk more about users and security, so make sure to check that out. Okay, friends! See you next time!
