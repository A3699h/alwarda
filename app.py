''' create a desktop app to download playlists from youtube '''
'''read pytube package file'''


# exec(open("example.py").read())
# exec(open("pytube/__main__.py").read())



import os
from tkinter import *
from pytube import YouTube
from pytube import Playlist
from tkinter import messagebox, filedialog
from tkinter.ttk import Progressbar
from threading import *
from win10toast import ToastNotifier
import threading
import urllib.request

# import tempfile

file_size = 0
save_path = ''
def progress(stream, chunk, bytes_remaining):
    ''' calculate the percentage of the file that has been downloaded '''
    percent = (100 * ((file_size - bytes_remaining) / file_size))
    progress_bar['value'] = percent

def startDownload(url, save_path):
    ''' start the download '''
    global file_size
    # get the download link

    try:
        # url = url_field.get()
        # print(url)
        # save_path = filedialog.askdirectory()
        # print(save_path)
        if save_path is None:
            return
        ob = YouTube(url, on_progress_callback=progress)
        # define the quality of the video
        # quality = ob.streams.filter(progressive=True, file_extension='mp4').order_by('resolution').desc().first()

        strm = ob.streams.filter(progressive=True, file_extension='mp4').order_by('resolution').desc().first()
        v_title.config(text=strm.title)
        v_title.pack(side=TOP)
        file_size = strm.filesize
        print(file_size)
        label_file_size.config(text='File size: ' + str(round(file_size / (1024 * 1024), 2)) + ' MB')
        label_file_size.pack(side=TOP, pady=10)
        progress_bar.pack(side=TOP, pady=10)
        # download button
        # btn = Button(root, text='Start Download', width=10, bg='red', fg='white', command=lambda: downloadVideo(strm, save_path))
        # btn.pack(side=TOP, pady=10)
        downloadVideo(strm, save_path)
        # send notification
        toaster = ToastNotifier()
        toaster.show_toast('Youtube Video Downloader', 'Downloaded ' + strm.title, duration=5)
    except Exception as e:
        print(e)
        print('Error')
def downloadVideo(strm, save_path):
    ''' download the video '''
    strm.download(save_path)
    # donwnload_link = strm.url
    # print(donwnload_link)
    # urllib.request.urlretrieve(donwnload_link, save_path, blocksize=1024, max_block=100)
    # messagebox.showinfo('SUCCESSFULLY', 'DOWNLOADED AND SAVED IN\n' + save_path)
# convert playlist url to individual videos 
def playlistDownload():
    url = url_field.get()
    save_path = filedialog.askdirectory()
    print(save_path)
    if save_path is None:
        return
    # check if the url is a playlist
    if 'list=' not in url:
        # look for the id of the video
        if 'watch?v=' in url:
            # split the url to get the id from v= to the end of the url or &
            # change the url to be in the format of a video
            url = 'https://www.youtube.com/watch?v=' + url.split('watch?v=')[1].split('&')[0]
            print(url)
            startDownload(url, save_path)
            # messagebox.showinfo('SUCCESSFULLY', 'DOWNLOADED AND SAVED IN\n' + save_path)

            
    elif 'list=' in url:
        # look for the id of the playlist
        # change the url to be in the format of a playlist
        url = 'https://www.youtube.com/playlist?list=' + url.split('list=')[1].split('&')[0]
        print(url)
        playlist = Playlist(url)
        playlist_title = playlist.title
        count = 0
        # convert the title to a valid directory name
        playlist_title = playlist_title.replace(' ', '_')
        # create a directory with the title name
        save_path = os.path.join(save_path, playlist_title)
        if not os.path.exists(save_path):
            os.makedirs(save_path)
        print(save_path)

        for video in playlist.video_urls:
            count = count + 1 
            print(video)
            startDownload(video, save_path)
        # send notification 
        toaster = ToastNotifier()
        toaster.show_toast('Youtube Video Downloader', 'Downloaded ' + str(count) + ' videos from ' + playlist_title, duration=5)
        
        
    else :
        messagebox.showinfo('ERROR', 'INVALID URL')


root = Tk()
root.title('Youtube Video Downloader')
root.geometry('500x600')
root.columnconfigure(0, weight=1)
# icon
# root.iconbitmap(r'assets\icon.ico')
# heading icon
file = PhotoImage(file=r'')
heading_icon = Label(root, image=file)
heading_icon.pack(side=TOP)
# url text field
url_field = Entry(root, font=('verdana', 18), justify=CENTER)
url_field.pack(side=TOP, fill=X, padx=10)
# download button
btn = Button(root, text='Start Download', width=20, bg='red', fg='white', command=playlistDownload)
btn.pack(side=TOP, pady=10)
# video title
v_title = Label(root, text='Video title')
# file size
label_file_size = Label(root, text='File size')
# progress bar
progress_bar = Progressbar(root, orient=HORIZONTAL, length=100, mode='determinate')
root.mainloop()
# Path: assets\icon.ico
# Path: assets\youtube.png


