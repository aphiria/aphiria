#!/usr/bin/env bash

set -e

GIT_REF="$1"

if [ -z "$GIT_REF" ]
then
    echo "No Git ref specified"
    exit 1
fi

declare -A dirs_to_repos=(["Api"]="api" ["Application"]="application" ["Collections"]="collections" ["Console"]="console" ["ContentNegotiation"]="content-negotiation" ["DependencyInjection"]="dependency-injection" ["Exceptions"]="exceptions" ["Framework"]="framework" ["IO"]="io" ["Middleware"]="middleware" ["Net"]="net" ["PsrAdapters"]="psr-adapters" ["Reflection"]="reflection" ["Router"]="router" ["Sessions"]="sessions" ["Validation"]="validation")

for dir in "${!dirs_to_repos[@]}"
do
    remote=${dirs_to_repos[$dir]}
    remote_uri="git@github.com:aphiria/$remote.git"

    if [[ $GIT_REF == "refs/tags/*" ]]; then
        tag_name=${GIT_REF/refs\/tags\//}
        tmp_split_dir="/tmp/tag-split"
        echo "Creating $tmp_split_dir for $remote"
        rm -rf $tmp_split_dir
        mkdir $tmp_split_dir

        (
            echo "Creating $tag_name for $remote"
            cd $tmp_split_dir
            git clone "$remote_uri"
            git checkout "1.x"
            git tag "$tag_name"
            git push origin --tags
        )
    else
        echo "Adding remote $remote"
        git remote add "$remote" "$remote_uri"

        echo "Splitting $dir"
        sha=$(./bin/splitsh-lite --prefix="src/$dir")

        if [ -z "$sha" ]
        then
            echo "Empty SHA"
            exit 1
        fi

        echo "Pushing SHA $sha from $dir to $remote"
        git push "$remote" "$sha:$GIT_REF"
    fi
done
