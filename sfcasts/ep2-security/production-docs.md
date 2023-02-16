# Production Docs

Coming soon...

Welcome back to API platform episode two. In the first course, we created an API to store our dragon treasures, but with no `security`. Any small, hairy-footed creature could sneak in a `back door` and we'd have absolutely no idea. So we're talking about `security`. Everything about `security`, like `authentication` should be used. `Session authentication` with a `login form`. How can we submit that via `AJAX` or do we need `API tokens`? And then `authorization` like denying access to entire `endpoints`.

Or showing and hiding results based on the user or even showing and hiding specific
fields based on the user. And we'll talk about totally custom fields, the `patch` HTTP
method, and setting up an API test system to be proud of. So it's gonna be super fun.
You should absolutely code along with me. Download the code course code from this
page. When you, after you unzip it, you'll find a `start` directory with the same code
that you see here. Pop open this nifty `README.md` file and go through all this setup
instructions. I'm all the way down here to starting the `Symfony` web server, so I'm
going to switch over. I have a new terminal already moved into the project and I'll run
`Symfony serve` --D to start that in the background.

Perfect. I'll cheat and hold `command` and click that to say hello to Treasure Connect.
This is the app that we created in `episode one`, though all we did in `episode one` was
work on our API. So we have a bunch of endpoints for treasures and have a bunch of
endpoints for users. That's what we did in `episode one`. This homepage is new. I built
this. This is a small view application that basically submits a login form. This
doesn't work yet. It's gonna be up to us to bring that to life. Now before we dive
into security, one question I sometimes get is, Hey Ryan, the interactive docs are
super nice, but could I hide them on production? Because maybe your API is private,
it's just meant for your JavaScript. So you don't want to advertise to the world that
you have these endpoints. I'm not sure that hiding these isn't really necessary
because even if you hide the documentation, these endpoints still exist. So you still
need proper security on them. But yes, hiding them is possible. So let's see how to find
your terminal. And I'm gonna run

```terminal
php bin/console config:dump api_platform
```

. Remember,
this is the `command` that will show us all the possible configuration for API Platform.

And if you look around in here, I'm actually gonna search for swagger.

There we go. You find a section here with things like `enable_swagger`, `enable_swagger_ui`, `enable_redoc`, `enable_entrypoint`, and `enable_docs`. Okay, what does all that mean? First I wanna show you what ReDoc is, cause we didn't talk about that in the first tutorial. So this is the swagger version of our documentation. But there's a competing format called ReDoc and you can actually click down here on ReDoc and you can see this our same API platform in a really nifty thing here. So it's this big long page, but you can click on the endpoints over here and it's just a different way of documenting it. So this is available to you. Anyways, back over here, we have quite a bit of actual little configuration here. Oops, did not mean to do that. So we're gonna see what these do. But broadly `enable_swagger` really more means enable OpenAPI documentation. Remember that's the JSON documentation that powers the swagger and ReDoc API docs. Then these are actually whether or not you literally want to show those two types of interactive documentation. And then down here `enable_entrypoint` and `enable_docs`. These are two things that will actually,

That actually adds new routes to our application. So, as we `disable` this, it's going to `remove` different routes. So, probably didn't make a lot of sense. So, let's see this in action. So, let's pretend we want to `disable` the docs. So, let's go over to config packages, API platform, `yaml`. And to start, I'm going to say `enable docs: false`. Now, as soon as you do that and `refresh`. Alright, our API documentation is gone, but it's a 500 error. So, when you `enable docs: false`, it literally `removes` our documentation. So, actually, let me show you here. Going to `slash API` was always kind of a shortcut to get to our documentation. The real way you were supposed to get to the docs was `slashapi/docs` or `slashapi/docs.json` or `JSON-LD`. You can see these are now all 404 because we just `disabled` the route. If it loads though, so yay, our documentation is gone. Unfortunately, if you go to just `slashapi`, this actually isn't a documentation page. This is known as our `entry point`. It's our homepage for our API. And you can now see that. And since that is still working but it doesn't know what to do, it tries to link to the API documentation. That doesn't work. So, it's a 500 error. So, let's now go over here and it'll say `enable entry point: false`.

False. Now gonna slash API beautiful is a `404`. All right, so we know we can go to
slash API slash `treasures.JSON` or `JSON-ld`. Perfect. What if we just go to slash
API slash treasures? That is unfortunately still a `404` error. Remember when our
browser makes a request to URL, it sends an `Accept` header that says that we want
`HTML`. So this is actually us asking our API for an `HTML` version of this URL.
And the `HTML` version is the documentation. So it tries to link to the documentation `404`
error.

<affirmative>.

So disable this, we can kind of communicate through the system that we don't have
Swagger or API documentation at all. And the way we can do that is we can just say
`enable_swagger: false`. When we do that, we actually get a 500 error that says, Hey,
you can not enable the Swagger UI without enabling Swagger, fix this by enabling Swagger via the configuration `"enable_swagger: true"`. So we'll also
say, `enable_swagger_ui`, `false`,

And now closer 500 error, it says serialization for the format `"html"` is not supported. So we're still requesting this URL and asking the API to return in `HTML` format. But now that we don't have ReDoc or any documentation, our API is like, I don't know how to return an `HTML` version of this. And in reality, for totally disabling the documentation, we don't need an `HTML` format anyway. So what we need to do is disable the `HTML` format. So the way we do that very simply is we take that off of `formats` and actually we have one other spot where we need to do that. In `src/Entity/DragonTreasure`. When we added our custom `CSV` format, let's see here it is, we repeated all the formats including `html`, so take `HTML` off there as well.

And when we refresh now, got it. Since there's no HTML format, it defaults to `JSON-LD` and our docs are now totally disabled. And it's kind of just an interesting exercise to see all the moving pieces. Now, if you wanted to disable this just for production, you could set all these to an `environment variable` that you have set to `true`, and that you activate only in production mode. All right? But I do like our documentation, so I'm going to remove this. I'm going to `undo` this change, and I'm going to `undo` this change as well. Well, let's get our documentation back. Love it. All right, next, let's have a chat. You have an `API`. You need a way to log into that `API`. Do you need `API tokens` or something else? Let's find out.
