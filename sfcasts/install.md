# Install

Coming soon...

Welcome, API Platform slash Dragon fans, to Episode 3 of our API Platform series. The
episode where things, frankly, take a turn for the serious. We're going to do some
very complex, very cool stuff. This tutorial was kind of tough for me to write
because we are doing really complex stuff and I want it to be awesome. I think you're
going to be very happy with the results. To review in Episode 1, that was our
introduction. We talked about pagination, filtering, and we talked a lot about
serialization, so how our API resource classes are turned into JSON and how the JSON
is turned back into our API resource classes. Episode 2 was about security. We talked
about things like state processors, which is how our data is saved. We talked about
custom fields, validation, voters, and more. All good stuff. But so far, all of our
API resource classes, so for our tutorial, the Dragon, Treasure, and the User,
they've all been entities. That's fine, but if your API starts to look different than
your entities, this adds complexity. You have to start leveraging groups so that you
have a field in your entity, but it's not in your API, or you have to do tricks to
add new fields to your API that don't live in your entity. At some point, it can
become a lot easier, a lot clearer to stop using your entity and create custom
classes for your API. That's going to be the big focus of this tutorial. We're really
going to break this down into two different parts. First, we're going to get a deeper
look into the concept of state providers and state processors in API platform, which
are basically the core to everything. We're going to use that to add custom fields
and run code when a field changes, like when something becomes published. The second
part of this tutorial is we're going to dive into that world of API resource classes
that are not entities. All right, people, let's do this. As always, if you want to
get the most out of this tutorial, you should absolutely download the course code and
code along with me. Come on, it's way more fun than just listening to my voice. When
you download the course code and unzip it, you'll find a start directory with the
same code that we have here, including the all-important readme.md file, which is
going to contain all the details to get this tutorial running. The last step will be
to spin over, open a terminal into the project, and run symphony-serve-d to start the
built-in web server at 127.0.0.1,8000. Say hello to Treasure Connect. This is the
same project we worked on in episode two and episode one. I've made a few small
changes to it, fixed a few deprecations, but mostly this is the same thing that we
were working with before. We don't need to worry about logging in right now. The most
important thing is we go to slash API, we can see our two resources, Treasure and
User. This is fairly complex. We have sub-resources. We have lots of different custom
rules, custom fields, but again, the most important thing to note is that both of
these, Dragon, Treasure, and User. The API resource attribute is above our entity
class, so that's going to be the big thing that we're going to be working on a little
later in this tutorial, creating dedicated classes that are not entities to represent
our API. Before we hop in, I'm going to go to the API platform docs and search for
API platform extending. This is one of my favorite pages on the API platform
documentation. It's just answers the question about what are all the different ways
that I can extend API platform? For example, state processors can be a way to run
code before or after you save something out of the database, which is something we
talked about in the last tutorial and something we're going to talk about more really
soon. I want you to know about this resource, but I also want to mention a couple of
things we're not going to talk about. We're not going to talk about building custom
controllers. You'll notice that it's not even in this list. The reason is that
there's almost always a better way, a different extension point when you need to do
something custom. We're also not going to talk about event listeners. Down here, you
can say kernel events. It's for the same reason. There are almost always different
extensions points you can use if you feel like you need to listen to an event. These
events only work for REST. They don't work for GraphQL. I happen to know in the next
version of API platform 3.2, these kernel events are probably going to go away in
favor of a new internal system that leverages the state providers and state
processors anymore. Another thing we're not going to show are these DTOs, at least
not in the way that they are talked about here. This DTO article talks about
something called using input and output classes. This is something that's not really
recommended anymore with API platform. The reason, and this is actually a quote from
talking with some of the core developers, is that a resource or operation should be
marked on the class it's representing. What we recommend now is to have a class per
URL. I'm not going to go too far into this. Instead of these input and output things,
we're going to favor more just creating ... Maybe I don't even need to talk about
DTOs at all. Next up, let's leverage a state provider and add a totally custom field
that, unlike the previous tutorial, will have proper documentation inside of our API.
