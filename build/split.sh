#!/usr/bin/env bash

CURRENT_BRANCH="master"
REPOS=(Api Configuration Console Exceptions Middleware Net RouteAnnotations Router Serialization)

function split()
{
    SHA1=`splitsh-lite --prefix=$1`

    if [ -z "$SHA1" ]
    then
        echo "Empty SHA" 1>&2
    fi

    git push $2 "$SHA1:refs/heads/$CURRENT_BRANCH" -f
}

git checkout $CURRENT_BRANCH
git pull origin $CURRENT_BRANCH

for repo in ${REPOS[@]}
do
    lowerrepo=$(echo "$repo" | awk '{print tolower($0)}')
    git ls-remote --exit-code $lowerrepo

    if test $? = 1;
    then
        git remote add $lowerrepo git@github.com:aphiria/$lowerrepo.git
    fi

    split "src/$repo" $lowerrepo
done
