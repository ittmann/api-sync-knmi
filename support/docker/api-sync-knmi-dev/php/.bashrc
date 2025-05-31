# Set prompt
force_color_prompt=yes
if [ -n "$force_color_prompt" ]; then
    if [ -x /usr/bin/tput ] && tput setaf 1 >&/dev/null; then
        # We have color support; assume it's compliant with Ecma-48
        # (ISO/IEC-6429). (Lack of such support is extremely rare, and such
        # a case would tend to support setf rather than setaf.)
        color_prompt=yes
    else
        color_prompt=
    fi
fi
if [ "$color_prompt" = yes ]; then
    PS1='[\[\033[1;33m\]\u@\h\[\033[00m\]:\[\033[01;36m\]\w\[\033[00m\]]\$ '
else
    PS1='[\u@\h:\w]\$ '
fi

# add handy aliases
alias ls='ls --color=auto'
alias grep='grep --color=auto'
alias fgrep='fgrep --color=auto'
alias egrep='egrep --color=auto'

# some more ls aliases
alias ll='ls -alF'
alias la='ls -A'
alias l='ls -CF'

# enable programmable completion features
if ! shopt -oq posix; then
  . /usr/share/bash-completion/bash_completion
fi

# source: http://tychoish.com/rhizome/9-awesome-ssh-tricks/
ssh-reagent () {
  for agent in /tmp/ssh-*/agent.*; do
      export SSH_AUTH_SOCK=$agent
      if ssh-add -l 2>&1 > /dev/null; then
         echo Found working SSH Agent:
         ssh-add -l
         return
      fi
  done
  echo No running ssh agent to connect to
  eval `ssh-agent -s`
  ssh-add
}
ssh-reagent

export SHELL=/bin/bash
eval "$(/application/bin/console completion )"
