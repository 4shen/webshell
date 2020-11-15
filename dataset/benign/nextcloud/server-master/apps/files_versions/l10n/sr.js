OC.L10N.register(
    "files_versions",
    {
    "Versions" : "Верзије",
    "This application automatically maintains older versions of files that are changed." : "Ова апликација аутоматски одржава старије верзије измењених фајлова.",
    "This application automatically maintains older versions of files that are changed. When enabled, a hidden versions folder is provisioned in every user’s directory and is used to store old file versions. A user can revert to an older version through the web interface at any time, with the replaced file becoming a version. The app automatically manages the versions folder to ensure the user doesn’t run out of Quota because of versions.\n\t\tIn addition to the expiry of versions, the versions app makes certain never to use more than 50% of the user’s currently available free space. If stored versions exceed this limit, the app will delete the oldest versions first until it meets this limit. More information is available in the Versions documentation." : "Ова апликација аутоматски чува старије верзије фајлова који су се изменили. Када се укључи, у свакој корисничкој фасцикли се додаје још једна скривена фасцикла у коју се смештају старије верзије фајлова. Корисник се у сваком тренутку може вратити на старију верзију фајлова кроз веб интерфејс, с тим да замењени фајл постаје исто једна верзија. Апликација аутоматски управља фасциклама са верзијама да би се осигурала да корисник не дође до квоте због чувања верзија.\n\t\tУз истицање верзија, апликација верзионисања се стара да се никад не користи више од 50% корисничког слободног простора. Уколико ускладиштена верзија прелази ову вредност, апликација ће кренути да брише верзије почевши од најстарије све док се не падне испод границе од 50%. Још информација је доступно у документацији апликације за Верзионисање.",
    "Failed to revert {file} to revision {timestamp}." : "Не могу да вратим {file} на ревизију {timestamp}.",
    "_%n byte_::_%n bytes_" : ["%n бајт","%n бајта","%n бајтова"],
    "Restore" : "Врати",
    "No other versions available" : "Нема доступних других верзија"
},
"nplurals=3; plural=(n%10==1 && n%100!=11 ? 0 : n%10>=2 && n%10<=4 && (n%100<10 || n%100>=20) ? 1 : 2);");
