# Custom Resource

Coming soon...

So far we have two API resource classes, Treasure and User. Both of these, as we
know, are actually entity classes. But having your API resource on an entity class is
not a requirement. You can create any normal boring PHP class you want, add this API
resource attribute to it, and bam, it can become part of your API. Well, we need to
do a little bit more work, but that's what we're going to find out. Now, why would
you want to create a custom class for your API instead of using an entity? Well,
there are two main reasons. The first reason is that the data you're serving doesn't
come from the database, or it comes from maybe a big mixture of a bunch of different
database tables. The second reason is that the data you're fetching is coming from
the database, but your API looks different enough from your entity that you want to
keep things cleaner by having a class for your API separate from your entity class.
We're going to go through both cases starting with the first, when your data comes
from somewhere other than a database. So here's the setup. Each day we post a unique
quest for our dragons to complete. We want to expose these quests as a new API
resource. You'll be able to list the quests. All the past quests fetch a single quest
by date, or update the status of a quest if you complete it. So all easy stuff for
us. But we're not going to store this data in the database. We're going to pretend
that this data is coming from somewhere else. So since this data is in the database,
we're not going to create an entity, we're just going to create a totally new class.
And I want you to put it in this API resource directory. This is actually added for
us by the API platform recipe. When we originally installed API platform, it's kind
of supposed to be the home for your API resource classes. So new PHP class, let's
call it daily quest. And then to make this part of your API, we just add API resource
to it. That's it. Check it out instantly. Bam, it's in our API documentation. Though,
it is a little odd. Notice the get single is missing. So normally, there's like
something slash API slash treasure slash ID, that doesn't exist up here. And we'll
talk about why that doesn't exist yet in a second. Now, one other thing to know is
that by default, API platform looks for this API resource attribute inside the entity
directory and the API resource directory. So that's why we put the class there.
Though, this can be configured in config packages API platform dot yaml with a
mapping paths configuration. I won't show it here because we don't need to configure
it. But you can have it point at different directories if you want to. All right, so
how could this possibly be part of our API? It's just a class, it doesn't even have
any properties yet. Well, if you try the get collection endpoint, and it executes, we
get a 404. So not actually working. And if you try the post endpoint, we'll just send
empty data. It actually gives you a 201 status code as if it was successful. But I
can tell you behind the scenes, nothing happened. No data was created or saved or
anything. It's a lie. So looking back at my favorite upgrade part of the
documentation that talks about providers and processors. This is the spot that shows
you all of the processors and providers that you get by default when you're using a
doctrine entity as your API resource. And it turns out the that's really the only
difference between an API resource on a random class and an API resource that's on an
entity. When you use API resource on an entity, API platform automatically gives you
a processor and provider for all of your operations. When you create a custom class,
you have no providers and no processors. That means that API platform doesn't know
how to load the data when you make a get request. And it doesn't know how to process
the data when you make a post request. That's going to be our job to do. So let's get
that going next.
