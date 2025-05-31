#!/bin/sh

apt-get update && \
apt-get install -y --no-install-recommends \
  bash \
  bash-completion \
  gettext \
  git \
  grep \
  less \
  locales \
  nano \
  openssh-client \
  recode \
  tzdata \
  unixodbc \
  unzip \
  wget \
  zip
