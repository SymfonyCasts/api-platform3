# Admin Edit

Coming soon...

We've got things set up so that only the owner of a treasure can edit it. So new requirement, admin users can also edit any treasure. That's a user that has a role. Underscore admin. Let's create a test for this. So how about public function test? Admin can patch to edit, treasure. And of course we're gonna need to start here is we need to create an admin user. And we'll do that with user, factory, colon, colon, create one, we'll pass that rolls. Roll role Role admin though I, and that will work just fine though. If we need to create lots of admin users in our tests, we can actually use a nice feature of Foundry to clean this up a bit. So I'm gonna go into user factory and we're gonna create something called a state method. This can go anywhere inside of here. It's gonna be a public function. It can be called anything you want, but it's gonna be a nice function that we can call to easily modify some data about the user we're creating. So for example, we could create a new method called with roles, or we pass it an array of roles and then we return self.

That's just for convenience. Then inside we can say, return this arrow, add state rolls, rolls. What this is gonna do is when we call this, if we pass it in a array of roles, add state is gonna basically take what we pass to Add State is going to become new data that's used to create this user. So when we create this, we can say this is gonna change a little bit. Now we can say User factory new. Instead of creating an object that actually creates a new instance of our user factory. And then of course we can call any methods on it. We want, like with roles roll admin. So we're kind of crafting what we want the user to look like. And then finally when we're done, we'll call create, not create one. In this case, create one as a static method. Somebody call Create one. That's a static method on the user factory. Once we have an instance of user factory, if we wanna create one, it's called Create. So it's just a nice little way to make things a little bit more explicit. But we can go even further. For example, on User Factory, which you create another statement that called

As admin that returns self and then returned this arrow with rolls Roll admin. So this is gonna make our test even more readable because we can just now say User factory, call and call New Arrow as admin Arrow Create and done. All right, let's actually put the test together now. So let's create a new treasure set to Dragon Treasure Factory call and call and create one cuz we're not passing any user. That's gonna be, we will create a new user in the background and assign it. So that means the A, the admin user will not be the owner. And then this arrow, browser arrow acting as the admin user. And then we're gonna patch to slash api slash treasures slash treasure arrow. Get Id sending some J S O N and we'll just do that same value. 1, 2, 3, 4, 5 that we did before. Search status 200 and assert and assert J S O matches value. 1, 2, 3, 4, 5. All right, let's try this. I'm gonna copy that. Test name we're run symphony PHB bin slash phb unit dash filter equals and okay, as expected, we haven't done anything to allow this yet. So we get 8 4 0 3 status code.

How do we fix this? Well, at first it's relatively easy, right? We have total control via this expression here. So we can do something like if granted parole, admin or, and then put parentheses around the other use case. But this is gonna work, but I know it looks a little crazy. Let's try the test first and 500 air. Hmm. Let's check out and see what's going on. I'll click to open this unexpected token name or value around P position 26. So that was an accident, but it actually brings up a really good point. So I didn't want, or in capitals, that's like an esque where I wanted or is lowercase and now it still fails. Oh, of course, because I need to also put it down here. So those two mistakes I made, it works. Now were actual real accidents just now, but it actually brings home a good point. These, uh, the security's getting complex here. These expressions are powerful but not very readable. Easy to make a mistake. So next, let's clean and centralize this with a voter.