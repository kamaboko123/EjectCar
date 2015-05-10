#include <stdio.h>
#include <fcntl.h>
#include <linux/cdrom.h>
#include <sys/ioctl.h>
#include <sys/stat.h>
#include <sys/types.h>
#include <unistd.h>

int main(int argc,char *argv[]){
    int fd, result, slot;
    
    if(argc != 2){
        printf("ERROR! デバイスファイルを指定してください\n");
        return(-1);
    }
    
    fd = open(argv[1], O_RDONLY | O_NONBLOCK);
    result=ioctl(fd, CDROM_DRIVE_STATUS, slot);
		
    close(fd);
		
    switch(result) {
        case CDS_NO_INFO:
            printf("NO_INFOMATION\n");
            return(-1);

        case CDS_NO_DISC:
            printf("NO_DISC\n");
            return(0);

        case CDS_TRAY_OPEN:
            printf("TRAY_OPEN\n");
            return(1);

        case CDS_DRIVE_NOT_READY:
            printf("NOT_READY\n");
            return(0);          

        case CDS_DISC_OK:
            printf("DISC_OK\n");
            return(0);

        default:
            printf("ERROR!\n");
            return(-1);
    }
}
