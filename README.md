# Github Deployment Script

## Description

This is a simple PHP script for Github's post-receive webhook

## Setup & Installation

1. Setup your repository on Github, if you haven't already
2. On the deployment server, get an SSH key for the web user.
	* Assuming this is apache, to find the web user, run: <code>ps aux | egrep "httpd|apache"</code> -- may be apache, _www, www-data. Let's assume it's www-data:
		sudo mkdir /var/www/.ssh/
		sudo chown www-data:www-data /var/www/.ssh/
		sudo -Hu www-data ssh-keygen -t rsa
		[default dir of /var/www/.ssh/ is fine, no password]
		sudo cat /var/www/.ssh/id_rsa.pub
3. Add the SSH key as a deploy key in Github, https://github.com/{{ repository }}/settings/keys
4. Clone the deploy script to a web-accessible location, git clone git://github.com/mboynes/github-deploy.git
5. Add a deploy-config.php file and in it define constants to override the defaults:
		# A regex matching the ref of the "push". <code>git pull</code> will only run if this matches. Default is the master branch.
		define( 'REF_REGEX', '#^refs/heads/master$#' );

		# Log location; make sure it exists
		define( 'LOG', '../logs/deploy.log' );

		# Where is your repo directory? This script will chdir to it. If %s is present, it gets replaced with the repository name
		define( 'REPO_DIR', dirname( __FILE__ ) . "/wp-content/themes/%s/" );

		# If set to true, $_POST gets logged
		define( 'DUMP_POSTDATA', false );

		# In your webhook URL to github, you can append ?auth={{ this field }} as a very simple gut-check authentication
		define( 'AUTH_KEY', 'enter-anything-you-want-here' );
6. Create the log directory and file, and make sure the user running apache (www-data) has write access to it.
7. In your Github repository, add a WebHook URL. Go to Settings/Admin -> Service Hooks -> WebHook URL (https://github.com/{{ repository }}/settings/hooks). Add in the URL for your deploy.php file, append ?auth={{ something }} if you set the AUTH_KEY. For example, http://deploy.example.com/deploy.php?auth=foobar123
8. Clone your repository, if you haven't already. Do so as the web server's user, <code>sudo -Hu www-data git clone git@github.com:your-account/your-repo.git</code>
9. If you go back to the WebHook URL page in Github, you can test your webhook. Check your log, and make sure it's all set!


## Copyright and license

Copyright 2012 Matthew Boynes

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this work except in compliance with the License.
You may obtain a copy of the License in the LICENSE file, or at:

   http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.