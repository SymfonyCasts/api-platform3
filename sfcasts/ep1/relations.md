# Relations

Coming soon...

In our app, each Dragon Treasure should be owned by a single dragon or user in our
system. So let's forget about the API for a second and just model that in the
database. So spin over it and run Bin Console, make entity. And let's add our modify
our Dragon Treasure entity because our dragon treasure needs an owner. So we're gonna
add an owner property and then this is gonna be a mini to one relation. Remember, if
you, and if you're, we're not sure which relation you need, you can always type
relation, they'll send you through a wizard to help you figure out which one. But we
want a many-to-one, and this is going to be related to user. It then asks us if the
Dragon treasure dot owner property is allowed to be null. Every dragon treasure must
have an owner. So I'm gonna say no here. And then it asks us if we wanna map the
other side of the relationship. So basically, do we want the ability to say, user
arrow, get dragon treasures in our code? I'm gonna say yes to this. You would say yes
to this for two reasons. One, this might be a useful thing to have in your code. And
two, as we're gonna see it a little bit later, this is gonna allow us to actually
show the Dr. Dragon Treasures for a user when we're using, when we're fetching our
user resource. So say yes to that. And the field name inside of user Dragon treasures
is just fine. Then finally, for orphan removal, say no, we're gonna talk about orphan
removal. And

Later, I think and done it, entered to exit that.

So

This has nothing to do with APAP platform. It's very simple. Our dragon treasure now
has a new, let's see, owner property with new get owner and set owner methods. And
over in user we have a new Dragon Treasures property, which is a one Toman packed to
TR Dragon treasure. And at the bottom of here we have get Dragon Treasures. Add
Dragon Treasure and remove Dragon Treasure. Very standard stuff. All right, so let's
make a migration for this symphony console. Make migration. We'll do our standard
double check. Yep, looks good. And then run this with Symphony console doctrine.
Migrations migrate and it explodes in our face. Okay, shouldn't be too surprising. We
have a bunch of Dragon Treasures ERO database when we try to add the owner ID to the
table where it's not Nu, our database has no idea what value to put for those
existing Dragon treasures. If this were already on production, we'd have to do a
little bit more of work. And we talk about that in our doctrine, doctrine tutorial.
But since this is not on production yet, the easiest thing to do is just fully reset
the database. So I'm gonna run Symphony console doctrine database,

Drop dash dash force, then doctrine database, create, then Doctrine, migrations
Migrate, which will now work now that our database has no rose inside of it. Finally
run Symphony Console Doctrine Fixtures Load. So we get some new data in there. And
oh, this fails for the same reason it's trying to create Dragon Treasures without an
owner. So to fix that, there's actually two ways in that I kind of like doing 'em
both in Dragon Treasure Factory and get defaults. We'll add a new owner field here
and called User Factory Call on Calling. No, I'm not gonna go into the specifics of
fa uh, founder has really good documentation on how to work with relationships, but
this is gonna basically make sure that if we l but what this would do if we did
nothing else, this would create a new user every time it created a Dragon Treasure
and relate them. So that's nice to have as a default. But in our app fixtures, I'm
gonna do something a little cooler. Let's move Dragon Treasure after User Factory and
then pass a second argument here, which is a way for us to override the defaults for
each time. So by passing a callback every time it, it's gonna should call this
callback 40 times.

And here we can return an array of data that should override those defaults. So I'm
gonna say owner and we can say User factory call on, call on random. So that's cool.
It'll find a random user object and set that as the owner. So we'll have 40 Dragon
treasures each assigned to one, randomly assigned to one of these 10 users. All
right, try Symphony Console doctrine fixtures load again. This time it's working. So
on a very high level, all we basically just did was add a new owner property to
Dragon Treasure and a new Dragon Treasures property, property over to user. So it
shouldn't be too surprising if we go and make, use our Get collection endpoint for
our treasures, the new property field does not show up in our api. That makes sense
that property is not inside of our normalization group. So if we want to expose the
owner property in the api, just like any other field, we need to add groups to it. So
I'm actually gonna copy the groups from Cool Factor and paste them here. This means
we'll be able to set the owner when we set the owner and also the owner should be
returned to us.

And yes, later on we're gonna, we'll learn how to set the owner automatically so that
you don't need to, so that the A API user doesn't need to actually send that. But for
now, we'll let them set it manually. All right, let's try this. I won't even refresh,
I'll just hit execute and beautiful. It's got owner and very interesting. It's set to
a url. We're gonna talk more about this, but that's the default behavior. When you
have relationships, it sets, it doesn't set it to the API platform. Could just set
this to the, ID like owner one, but this is way more useful because this tells your
API client what you were it could go to to get more information about that owner. All
right, let's try writing that field. So let me close up this endpoint. Let's create a
new dragon treasure. Just say Dragon treasure. Highly valuable. Super cool. Oh, you
know what? Actually I don't see my owner ID yet owner here yet. I could add it
manually. I wanna refresh. I'm gonna copy that and refresh real quick cuz I want you
all to see that owner does show up there

Since we added it to our group. So lemme paste that and now I'll add owner. And for
owner, let's just go find an ID down here on our, okay, cool. I have an owner whose
ID is just one. So I'll just put one right there. Hit execute and let's see. Ah, 400
status code. And check this out. Expected I r I or nested document for attribute
owner integer given. So I passed it. The ID of the owner it, it doesn't like that.
What should we put here? Well, it tells us in iri, of course, what the heck isn't I,
I, let's find out next.

