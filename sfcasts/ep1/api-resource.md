# Creating your First ApiResource

The point we're about to build an API so that dragons can show off their treasure.
Right now our project doesn't even have any database entities, so we're going to need
one to store the individual treasures. To do that, find your terminal and first run
compose. Require maker-dev to install Maker bundle. Perfect. And then run bin
console. `make:entity`. Perfect. Let's call our entity Dragon Treasure. And then it's
going to ask us a question you maybe didn't see, have never seen before. Mark this
class as an API platform resource. Because API platform is installed. It's asking us
this question. I'm going to say no and we're, because we're going to do this step
manually in a second so that you can see it. All right, let's start adding some
properties. Name has a string. Type 2 55, not knowing the database. Then description
has a text type, not knowing the database. Let's do a, a value like how much the
treasure is worth. That'll be an integer. Also not know. Then you gotta have a cool
factor. That's going to be from one to 10. So let's do an integer on that. Then A
created at datetime. Immutable is perfect. And then finally, an is published,

Which will be a boo type. Also not, no. And then I'll enter to finish. So nothing
very special here so far. That created two classes. The Dragon Treasury Repository,
which we're not going to worry about. And the Dragon Treasure entity itself with id
name, description, value, and the other properties and the gitters and setters below.

So super boring though. There is one little bug in this version of Maker Your bundle.
It generated and is is published method. So let's just change that to a Git is
published. It's trying to be a little bit too clever. All right, so we have our
entity. Now we need a migration for it, but first we don't even have our database set
up yet. I'm actually going to set up the database via Docker. The doctrine bundle
gave us a nice Docker compose. That YAML file that boots up Postgres. So I'm going to
use that. So I'll spin on over and start Docker composed with Docker, compose up-D.
Perfect. Now if you want to use a, if you don't want to use.for some reason, then you
can start your own database engine and then in.N for MT. Local, just configure your
database. So you were all correctly. Because I'm using Docker and also the Symfony
binary, I don't need to configure anything. The Symfony Web server is going to
automatically detect, automatically set that database URL environment variable for
me. All right, so to make the migration run Symfony console, make migration Symfony
console is just like Bin Console, except because we're running through Symfony. It's
going to inject that environment variable so that it talks to the DA Docker database.

Perfect. And as usual, I'd like to spin over and check out the new migration. Make
sure it doesn't contain any surprises. Perfect. It's gray on the table. Dragon
Treasure. So now spin back over and run Symfony Consult doctrine. Migrations migrate.
Yay. All right. So we now have an entity and a database table. But if, but if you go
and refresh our documentation, there's still nothing there. What we need to do is
tell API platform to expose our new Dragon Treasure entity as an API resource. To do
this, go above the class and add a new attribute called API resource, and a tab to
add that use statement. That's it.

As soon as we do that in refresh, whoa, the documentation grows. It's now saying that
we have five different endpoints. A way to get all of the dragon treasures. A way to
get an individual dragon treasure. Create one, two endpoints to edit dragon
treasures, and one to delete a dragon treasure. And this is more than documentation.
These end points actually exist, and you can even try it. So I'll try it out here,
can execute and it doesn't actually return anything. You can see this is an empty
array erase set here because our database is empty, but it is working. And we're
going to talk about all these other fancy keys are here in just a little bit. They're
super important. Now, as you just saw, the easiest way to create an API is by adding
this API resource above your entity. One thing I do want to mention is you can also
add API resource, have this API resource attribute to classes that are not entities,
and that's something we're going to talk about in a future tutorial. This can often
be a nice way to separate what your API looks like from where your entity looks like
a little bit, especially in bigger APIs. But we'll talk about in the future. For now,
using API resource on top of our entity is going to work. Great. All right. Next

We need to talk about something important called Open api, which is the key to how
these interactive docs are being generated.
