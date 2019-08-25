#!/usr/bin/env bash

set -e

GIT_BRANCH="$1"
GIT_USER="$2"
GIT_ACCESS_TOKEN="$3"

if [ -z "$GIT_BRANCH" ]
then
    echo "No Git branch specified"
    exit 1
fi

if [ -z "$GIT_USER" ]
then
    echo "No Git user specified"
    exit 1
fi

if [ -z "$GIT_ACCESS_TOKEN" ]
then
    echo "No Git access token specified"
    exit 1
fi

dirs_to_repos=(["Api"]="api" ["Collections"]="collections" ["Configuration"]="configuration" ["DependencyInjection"]="dependency-injection" ["Exceptions"]="exceptions" ["IO"]="io" ["Middleware"]="middleware" ["Net"]="net" ["RouteAnnotations"]="route-annotations" ["Router"]="router" ["Serialization"]="serialization")

for dir in ${!dirs_to_repos[@]}
do
    remote=${dirs_to_repos[$dir]}

    echo "Adding remote $remote"
    git remote add "$remote" https://$GIT_USER:$GIT_ACCESS_TOKEN@github.com/aphiria/$remote.git >/dev/null 2>&1

    echo "Splitting $dir"
    sha=$(./bin/splitsh-lite --prefix="src/$dir")

    if [ -z "$sha" ]
    then
        echo "Empty SHA"
        exit 1
    fi

    # Push to the subtree's repo, and do not leak any sensitive info in the logs
    echo "Pushing $dir to $remote"
    git push "$remote" "$sha:refs/heads/$GIT_BRANCH" -f >/dev/null 2>&1
done
