#!/usr/bin/env bash

set -e

GIT_BRANCH="$1"
GIT_USER="$2"
GIT_ACCESS_TOKEN="$3"
GIT_TAG="$4"

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

declare -A dirs_to_repos=(["Api"]="api" ["Collections"]="collections" ["Configuration"]="configuration" ["Console"]="console" ["ConsoleAnnotations"]="console-annotations" ["DependencyInjection"]="dependency-injection" ["Exceptions"]="exceptions" ["IO"]="io" ["Middleware"]="middleware" ["Net"]="net" ["Reflection"]="reflection" ["RouteAnnotations"]="route-annotations" ["Router"]="router" ["Serialization"]="serialization" ["Sessions"]="sessions" ["Validation"]="validation")

for dir in "${!dirs_to_repos[@]}"
do
    remote=${dirs_to_repos[$dir]}
    remote_uri="https://$GIT_USER:$GIT_ACCESS_TOKEN@github.com/aphiria/$remote.git"

    # Push to the subtree's repo, and do not leak any sensitive info in the logs
    if [ -z "$GIT_TAG" ]
    then
        echo "Adding remote $remote"
        git remote add "$remote" "$remote_uri" >/dev/null 2>&1

        echo "Splitting $dir"
        sha=$(./bin/splitsh-lite --prefix="src/$dir")

        if [ -z "$sha" ]
        then
            echo "Empty SHA"
            exit 1
        fi

        echo "Pushing $dir to $remote"
        git push "$remote" "$sha:refs/heads/$GIT_BRANCH" -f >/dev/null 2>&1
    else
        tmp_split_dir="/tmp/tag-split"
        echo "Creating $tmp_split_dir for $remote"
        rm -rf $tmp_split_dir
        mkdir $tmp_split_dir

        (
            echo "Creating $GIT_TAG for $remote"
            cd $tmp_split_dir
            git clone "$remote_uri"
            git checkout master
            git tag "$GIT_TAG"
            git push origin --tags
        )
    fi
done
