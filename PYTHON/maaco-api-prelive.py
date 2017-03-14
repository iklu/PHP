import gzip
import os
import gzip
import shutil
import sys
import tarfile
import paramiko

nargs = len(sys.argv)

if not 2 <= nargs <= 5:
    print "usage: %s [release] " % \
          os.path.basename(sys.argv[0])
else:

    username = "maacoca"
    password = "maacocaprogrammers2016*"
    host = "52.42.18.19"



    release = "./release-" + sys.argv[1] + "/"
    app_directory = "./release-" + sys.argv[1] + "/Acme/"
    app_directory_tar_gzip_name = "./release-" + sys.argv[1] + "/Acme-dev-" + sys.argv[1] + ".tar.gz"

    # GET TAR
    try:
        os.stat(release)
    except:
        print ("Release folder creating..." + "\n")
        os.mkdir(release)

    print ("Connecting to stage..." + "\n")

    ssh = paramiko.SSHClient()
    ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    ssh.connect(hostname=host, username=username, password=password)

    sftp = ssh.open_sftp()

    local_path = release + 'temp.tar.gz'
    remote_path = '/var/www/maacoca/html/src/Acme-dev-' + sys.argv[1] + '.tar.gz'
    sftp.put(app_directory_tar_gzip_name, remote_path)


    # CREATE TAR ON STAGE
    print ("Creating the tar archive for downloading from stage..." + "\n")
    commands = ["cd /var/www/maacoca/html/src/;  sudo rm -rf Acme_old; sudo mv Acme Acme_old; sudo tar zxf Acme-dev-" + sys.argv[1] + ".tar.gz"]
    for command in commands:
        print "Executing {}".format(command)
        stdin, stdout, stderr = ssh.exec_command(command, get_pty=True)
        stdin.write(password + '\n')
        stdin.flush()
        for line in stdout.read().splitlines():
            print 'host: %s: %s' % (host, line)
        print stdout.read()
        print("No errors ")
        print stderr.read()

 
    print remote_path
    sftp.close()
    ssh.close()


    print ("Job successfully done!!!" + "\n")