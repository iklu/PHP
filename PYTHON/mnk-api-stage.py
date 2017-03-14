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

    username = "mnk"
    password = "mnkprogrammers2016*"
    host = "52.24.27.64"
    pem = "/home/ovidiu/.ssh/mnk-rearc-staging-api.pem"


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
    k = paramiko.RSAKey.from_private_key_file(pem)
    ssh = paramiko.SSHClient()
    ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    ssh.connect(hostname=host, username=username, password=password, pkey=k)

    sftp = ssh.open_sftp()

    local_path = release + 'temp.tar.gz'
    remote_path = '/var/www/meinekeapi/html/src/Acme-dev-' + sys.argv[1] + '.tar.gz'



    # CREATE TAR ON STAGE
    print ("Creating the tar archive for downloading from stage..." + "\n")
    commands = ["cd /var/www/meinekeapi/html/; git checkout master;  cd src; sudo tar czvf Acme-dev-" + sys.argv[1] + ".tar.gz Acme; cd ..; git checkout dev;"]
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

    print ("Downloading the tar archive " + "Acme-dev-" + sys.argv[1] + ".tar.gz" + " from stage..." + "\n")
    sftp.get(remote_path, local_path)

    shutil.move(local_path, app_directory_tar_gzip_name)
    # doar pt upload
    # sftp.put(localpath, remotepath)
    print remote_path
    sftp.close()
    ssh.close()


    print ("Job successfully done!!!" + "\n")