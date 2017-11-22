# rfid-sonos

An application to listen to keyboard inputs of a RFID reader to start
playing playlist on Sonos speakers. Assign playlists to RFID cards and
start playing music by scanning RFID cards.

[Read the story behind](http://0xc2a0.com/001/avoiding-screen-devices.html)

## Requirements

1. A Raspberry Pi with Network connectivity
1. An USB RFID Reader (for example KKmoon M301) 

## Install on Raspberry Pi 

Build the executable phar file:
```
box build
```

Install the phar file on the Raspberry as `/home/pi/rfid-sonos.phar` 

## Configuration

For a minimal setup you have to configure at least the Sonos room and
which keyboard should be used:

```
~/rfid-sonos.phar config --room "Living Room"
~/rfid-sonos.phar config --keyboard /dev/input/event0
```

To test everything before running the systemd service, start the
application manually:

```
~/rfid-sonos.phar config run 
``` 

If you scan a card that has not yet been assigned to a playlist 
you can do so:

```
~/rfid-sonos.phar assign --card 2,0,4,8,3,5,1,1,5,2 --playlist Fun
```

## Install Systemd Service

Create /etc/systemd/system/rfid-sonos.service
```
[Service]
Type=forking
ExecStart=/home/pi/rfid-sonos.phar run
StandardOutput=null
Restart=on-failure
StandardError=syslog
User=pi

[Install]
WantedBy=multi-user.target
Alias=rfid-sonos.service
```

