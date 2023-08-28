# Micro Mapper

Coming soon...

Doing the data transformation like from the User API to the User Entity, or the User
Entity to the User API, is the only part of our provider and processor that isn't
generic and reusable. Darn it, if it weren't for this code, we could quickly create a
Dragon Treasure API class and do this whole thing over again with almost no work.
Fortunately, this is a well-known problem, basically called data mapping. And for
this tutorial, I tried a few libraries to help with this, most notably the JanePHP
automapper bundle, which is super fast and advanced. And fun to use. However, it also
seemed to not be quite as flexible as I needed it to, and extending it started to
look kind of complex. I honestly got stuck in a couple spots. So I hope that I am
proven wrong by that library. Maybe I was just using it wrong. Because it's very
cool, and I know some of the people that work on it, and they're awesome. So the
point is, we're not going to use that library instead to handle the mapping. I
created my own very small library that we're going to reuse in this tutorial. It's
easy to understand, and gives us full control, even if it's not quite as cool as
JanePHP's automapper. So let's get it installed. Composer require symphonycasts slash
micromapper. Sounds like a superhero. All right, when that installs, we now have a
new micromapper service that we can use. So here's how we're going to do it. Let's
start with the processor. So up top here, we are going to inject a new private
micromapper interface, micromapper service. And down here, for all the mapping stuff,
if you want you can copy this. We're going to kind of reuse a bunch of this in a
second, in a different spot. And all we're going to say is return this arrow
micromapper, arrow map. And this has two big main arguments, the from object, which
is going to be our DTO from, and the to class. We want to convert this to a user
entity, so user colon colon class. And that's it. Okay, that's not it. It's not going
to quite work yet, but let's actually try running our test post to create a user
test. And it fails with 500 error. But the really interesting thing is what that 500
error is. Let me actually view page source so we can read this even better. So it
says no mapper found for user API to user, and this is coming from micromapper. So
it's basically saying, hey, I don't know how to convert a user API object to a user
object. Micromapper is very not magic. We do everything by hand. So the way we're
going to do it is we're going to create a class that explains how to do this
transformation called a mapper class. So these are kind of fun, I'm going to create a
new, close a few things, a new mapper directory and source. And inside of there, a
new PHP class called, how about user API to entity mapper. So we're going from user
API to entity. There's two requirements on this needs to implement a mapper
interface. Perfect. And then above it to describe what it's mapping to and from, from
and to, we're going to add an as mapper attribute with from user API, colon, colon
class, and to user, colon, colon class. That's how the micromapper is going to know
to use this when we're doing that direction. Now because we've implemented this
interface, I'll go to code generate or Command N on a Mac and implement the two
methods that we need, which are load and populate. And just to start, I'm going to DD
the from object in the to class inside of the load method. Now just by creating this
and giving it this as mapper, when we use the micromapper and try to do that
transformation, it should call our load method. So let's try it. Let's rerun the test
again. And I have a syntax error. Let's actually get rid of that. And got it perfect.
So there's the user API object we're passing, and it's passing us the user class. So
there's two methods inside of here. The purpose of load is basically to load the to
object and return it. So in our case, it's going to be loading it from the database.
So we're going from a user API to a user entity. We basically want to load the user
from the database or return a new user if we need it. So let me show you. I'm going
to create public function underscore, underscore construct. We will inject our normal
user repository, user repository. And down here is going to be kind of the same code
that we saw earlier. I'm actually going to start just to help my brain, I'm going to
say DTO equals from. And then I'm gonna say assert DTO is an instance of user API. So
it's going to help my brain and help my editor. And then the user entity is going to
be like this. If our DTO has an ID, then we're going to use this error user
repository arrow find DTO arrow ID, else, we're going to create a new user object.
That simple. And that's why the purpose of the load method is either load it from a
data source, or just create a new object on there. And just for clarity, just to help
give me a better air, for some reason, we don't have a user entity, I'll throw a new
exception, kind of like we did before, user not found that shouldn't happen down here
or return user entity. So we've kind of initialized our to object and returned it.
Now whatever we return from the load method, our user object is immediately get micro
members then immediately going to call populate passes the from object and passes the
user entity the to object. So to show you, I'll D, D from n two. And we run it.
Perfect. So here's our from user API object. And here is our new user object right
there. Now, if you're wondering why we have both a load method and a populate method,
it really seems like these can just be one method. And you're right, there's a
technical reason why they're separated, it's going to come in handy later when we
talk about relationships. But right now, you can really almost think of this as one
method here. This is where we kind of load the object from the database. And then
down here is going to be where we populate all the properties, taking them from the
from and putting them onto the two. And once again, for my own sanity, I'm going to
say DTO equals from and assert DTO as an instance of user API, I should do the same
thing for from I'm gonna say entity equals to and assert entity is an instance of
user. So now my code down here is gonna be really normal and boring. I'm gonna just
type this quickly. So this is the same code that we had earlier. DTO. DTO. DTO. So
I'm gonna do DTO. DTO. DTO. DTO. DTO. DTO. DTO. So this is the same code that I had
earlier. So I'm gonna do DTO. DTO. DTO. DTO. DTO.

So that code is the exact same code we had before, at the bottom I'll return Entity and the other thing here is I'm using the user password hasher, so let's also make sure we have that up here, private user password hasher interface, user password hasher. So same code, just kind of moved to a different location. All right, now let's try that test again. And it passes! So this is huge, we've offloaded all this work to this mapper, and our processor, look, it's now basically completely generic. We can remove the old user password hasher, we don't need that. And we also don't need the user repository from up here, I'll even remove those use statements. Love that. So we still have to do all the work of mapping, but we now have it in this nice central location. So let's repeat this also for the provider. So let me close the processor, and let's open up our provider. In this case we're going from the entity over to user API, so I'm going to copy all this code here, and delete it, and just like we did in the other spot, we're going to auto-wire the micro mapper interface, micro mapper, and then down here, this simplifies greatly to return this arrow micro mapper, arrow map, we're going to go from our entity to our user API class. And of course if we tried that right now, that would 500 error, because we don't have a mapper for that. So let's jump straight to creating it. So in source mapper, we'll create a new class called user entity to API mapper, we'll implement the mapper interface, we'll say, put our little as mapper, in this case, we're going from user colon colon class to user API colon colon class, then I will implement both the methods that we need, and kind of start the same way as before, I'm going to say entity equals from, just for my own sanity, and then assert entity as an instance of user to help out my editor, and then down here we'll create the DTO, there's no querying the database for the DTO, we're always going to be creating a new, fresh user API object, and then we're going to set the ID onto it, so, and return DTO. So in this case, the job of the load method in this case is just to create the user API object, but make sure that it has the identifier on it, so that's all the work that we need to do in load. All the other work will be done down here in populate, so I'll very quickly start the same way I was doing before, entity equals from, DTO equals to, those two asserts, and then down here it's the exact same code that we had before, we're just transferring the data, at the bottom, I will return DTO, alright, let's try this, I'm going to go to my browser, let's just refresh this page, oh, full authentication required to access the resource, of course because we added security back on, let me pop to my homepage real quick, we can click this username and password, boop, go back and refresh, and it works, though we're missing some of our data, that's actually my fault, I did DTO equals new user API, so instead of modifying the two object I was being passed, I was actually creating a new one, so the original one was never actually getting modified, there we go, now that's working better, and it works, this is huge, so now our provider and our processor are completely generic, let's finish the process of making them work for any API resource class next, next.
