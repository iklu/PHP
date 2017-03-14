import gzip
import os
import gzip
import shutil
import sys
import tarfile
import paramiko

nargs = len(sys.argv)

if not 3 <= nargs <= 5:
    print "usage: %s [release] [version]" % \
          os.path.basename(sys.argv[0])
else:

    username = "mastermnk"
    password = "mastermnkprogrammers2016*"
    host = "52.25.254.77"
    pem = "/home/ovidiu/.ssh/mnk-rearc-staging-api.pem"

    version = sys.argv[2]

    main_css = "main.min.css"
    custom_js = "custom.min.js"
    vendor_js = "vendor.min.js"

    main_css_gzip = "main-v" + sys.argv[2] + ".gz.css"
    custom_js_gzip = "custom-a-v" + sys.argv[2] + ".gz.js"
    vendor_js_gzip = "vendor-v" + sys.argv[2] + ".gz.js"

    base_html = "./release-" + sys.argv[1] + "/AppBundle/Resources/views/default/base.html.twig"
    base_html_output = base_html + ".temp"

    search_resources = "front_resources"
    replace_resources = "front_theme"

    release = "./release-" + sys.argv[1] + "/"
    app_directory = "./release-" + sys.argv[1] + "/AppBundle/"
    app_directory_tar_gzip_name = "./release-" + sys.argv[1] + "/AppBundle-dev-" + sys.argv[1] + ".tar.gz"

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
    remote_path = '/var/www/mnk/master/html/src/AppBundle-dev-' + sys.argv[1] + '.tar.gz'



    # CREATE TAR ON STAGE
    print ("Creating the tar archive for downloading from stage..." + "\n")
    commands = ["cd /var/www/mnk/master/html/; grunt build; cd src; sudo tar czvf AppBundle-dev-" + sys.argv[1] + ".tar.gz AppBundle"]
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

    print ("Downloading the tar archive " + "AppBundle-dev-" + sys.argv[1] + ".tar.gz" + " from stage..." + "\n")
    sftp.get(remote_path, local_path)

    shutil.move(local_path, app_directory_tar_gzip_name)
    # doar pt upload
    # sftp.put(localpath, remotepath)
    print remote_path
    sftp.close()
    ssh.close()

    print ("Extract the tar archive to release folder..." + "\n")
    # EXTRACT TAR
    tar = tarfile.open(app_directory_tar_gzip_name)
    tar.extractall(release)
    tar.close()

    # Create scripts
    scripts_directory = "./release-" + sys.argv[1] + "/scripts/"

    js_folder = "release-" + sys.argv[1] + "/AppBundle/Resources/public/js/"
    css_folder = "release-" + sys.argv[1] + "/AppBundle/Resources/public/css/"

    dictionaryFiles = {
        css_folder + main_css: scripts_directory + main_css_gzip,
        js_folder + custom_js: scripts_directory + custom_js_gzip,
        js_folder + vendor_js: scripts_directory + vendor_js_gzip
    }

    try:
        os.stat(scripts_directory)
    except:
        print ("Create scripts directory..." + "\n")
        os.mkdir(scripts_directory)

    print ("Create gzip files..." + "\n")
    for key, value in dictionaryFiles.items():
        f_in = open(key, 'rb')
        f_out = gzip.open(value, 'wb')
        f_out.writelines(f_in)
        f_out.close()
        f_in.close()
        print value, 'contains', os.stat(value).st_size, 'bytes of compressed data'
        os.system('file -b --mime %s' % value)


        # Modify    base.html.twig
        print ("Modify base.html.twig for the gzip versions...")
    dictionaryBase = {
        main_css: main_css_gzip,
        custom_js: custom_js_gzip,
        vendor_js: vendor_js_gzip,
        search_resources: replace_resources
    }

    fp1 = sys.stdin
    fp2 = sys.stdout

    fp1 = open(base_html_output, "w")
    fp2 = open(base_html, 'r')
    data = fp2.read()
    fp2.close()
    for key, value in dictionaryBase.items():
        data = data.replace(key, value)
    fp1.write(data)
    fp1.close()

    shutil.move(base_html_output, base_html)

    print ("Pack the modified archive for deploy..." + "\n")
    # PACK the tar.gz
    tar = tarfile.open(app_directory_tar_gzip_name, "w:gz")
    tar.add(app_directory, arcname="AppBundle")
    tar.close()

    print ("Delete AppBundle directory ..." + "\n")
    # DELETE APP FOLDER
    shutil.rmtree(app_directory)

    print ("Job successfully done!!!" + "\n")