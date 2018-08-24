How to get set up developing on CBTS:

- clone this repo
- set up mysql, php and node
- copy `credentials.php.example` as `credentials.php`.
- create a database - see dbconfig for details. put the mysql password in `credentials.php`
- ensure the path to `node` in `credentials.php` is correct
- if you want the Post! button to work, clone `https://github.com/BooDoo/traceryhosting-send_post`, run `npm update` to fetch the dependencies, and update the path to `send_post.js` to `credentials.php`
- you can spin up a dev server with the builtin php server like so: `php -S localhost:8000`

- now go check out `https://github.com/BooDoo/traceryhosting-backend`!

If you can't tell by the fork info, this is all heavily based on [v21/traceryhosting-frontend](https://github.com/v21/traceryhosting-frontend)
