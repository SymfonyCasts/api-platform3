# User Entity

Coming soon...

We won't talk about security in this tutorial, but even still we do need the concept
of a user because each treasure in the database is going to be owned by a user. And
we'll be able to see which, which of course by user I mean Dragon. And then we should
be able to see like which treasures belong to which user and maybe later, maybe even
users can message other users about their treasures. So let's create a user class,
find your terminal and run bin console. Make user, I could use make entity for this,
but instead I'll may use make user just so it sets up a little bit of the security
stuff that will need a future tutorial. So let's use user for the security class.
Yes, we are gonna store users in the database and then for the use email for the main
field. And then it asks us if we need to hash and check user passwords. If the hashed
user password is actually stored and checked in your system, you do. If you have a
system where your user submits a password but you validate on another server, you
don't need that. That means it's the other server that's gonna be handling passwords.
But I'm gonna say yes to this.

So this didn't do much. Fern Get Status created, the user entity, the repository
class, and then a small update in security of that yammel. If you open config
packages. The ammo, it's really basic in here. It just kind of set up our user
provider. Nothing special. And again, we'll talk about that in the future. Tutorial.
Inside the source entity directory, we have our U new user entity glass with ID email
rolls and password, and then the getters and setters below. So nothing too fancy.
This implements two interfaces that we need, but nothing that's, but those aren't
going to be important for us right now. Now I wanna add one more feel to my user
class, which is gonna be a username so that if dragons are talking to each other,
they have these cool usernames that we can show. So let's spin back over and let's
this time run, make entity. We'll update the user field. I'll add a username
property. There'll be a string. 2 55 is good. Nodding on the database is good and
done. Hit enter. One more time to exit. Awesome. And over here. Perfect. There's our
username field. And while I'm here, I'm actually gonna add a little unique true that
just makes it unique in the database.

Cool. So we have our new user entity class, so we need a migration for it back here,
terminal run, symphony console, make migration. Perfect. Then I'll spin over and open
that new migration file and yep, no surprises. It creates the user table. So close
that up and run the migration with Symphony Console Doctrine. Migrations migrate.
Beautiful. All right, so if we're gonna have this user entity, we probably want to
have some nice F fixtures data for it. So let's use Foundry like we did for our
dragon treasure. So run bin console, make factory, and we'll generate the factory for
user. So just like before in the source factory directory, we now have a U new class
user factory, which is really good at creating user objects. The only thing we really
need to tweak in here is get defaults. I'm actually going to paste in new contents
for this class, which you can copy from the code block on this page. All this said
was update get defaults with some nice defaults. So password will be our password.
And then it added a little after instantiation hook to hash that password. Finally,
to actually create some fixtures with this. Open up at fixtures class

And we'll add user factory, colon, colon, create many, and let's just create 10. All
right, let's see if that worked. Spin over and run. Symphony console doctrine,
fixtures, load, and cool. No errors. All right, so we have a user class, we've got
the migration for it. We've even got data fixtures for it, but it is not yet part of
our api. If you refresh the documentation, there's still only treasure here. So let's
make this part of our API next.

