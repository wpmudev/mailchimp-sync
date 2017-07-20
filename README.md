# MailChimp Sync

## Development guide

### Branches
There are two main branches:

* `development`: Every development work should be done here first 
* `master`: Whenever a new version is ready, merge `development` branch into this one and release (See release section)

### Development workflow

MailChimp Sync contains a few automated tasks that helps the developer to make faster and less buggy releases.

#### Requirements:

1. Install nodejs: [https://github.com/joyent/node/wiki/installing-node.js-via-package-manager]
2. Execute `git submodule update --init --recursive` to download every submodule
3. Execute `npm install` to download all npm dependencies

#### Releasing versions

1. Make sure that the version in `mailchimp-sync.php` matches with the version in `package.json`, otherwise the build will fail.
2. Checkout `master` and merge `development` by using `git merge development`
3. Now execute `npm run release`. A new folder called `build` will be created where you can grab the zip file for the new version. Follow instructions to generate a new Git tag and upload the file to WPMU DEV.
4. Language files, JS Lint and text domains verification are done during the execution of this script so developer doesn't need to worry about these tasks.