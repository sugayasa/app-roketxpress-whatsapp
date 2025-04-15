<script type="module">
	import { initializeApp } from "https://www.gstatic.com/firebasejs/9.13.0/firebase-app.js";
	import { getDatabase, ref, onValue } from "https://www.gstatic.com/firebasejs/9.13.0/firebase-database.js";

	const firebaseConfig = {
		apiKey: '<?=FIREBASE_PUBLIC_API_KEY?>',
		authDomain: '<?=FIREBASE_PUBLIC_AUTH_DOMAIN?>',
		databaseURL: "<?=FIREBASE_RTDB_URI?>",
		projectId: '<?=FIREBASE_PUBLIC_PROJECT_ID?>',
		storageBucket: '<?=FIREBASE_PUBLIC_STORAGE_BUCKET?>',
		messagingSenderId: '<?=FIREBASE_PUBLIC_MESSAGING_SENDER_ID?>',
		appId: '<?=FIREBASE_PUBLIC_APP_ID?>',
		measurementId: '<?=FIREBASE_PUBLIC_MEASUREMENT_ID?>'
	};

	const app			=	initializeApp(firebaseConfig),
		  database		= 	getDatabase(app),
		  rtdb_appPath	=	'<?=FIREBASE_RTDB_MAINREF_NAME?>/',
		  currentACK	=	ref(database, rtdb_appPath + 'currentACK'),
		  newMessage	=	ref(database, rtdb_appPath + 'newMessage');

	onValue(currentACK, (snapshot) => {
		const lastAlias = localStorage.getItem('lastAlias'),
			dataCurrentACK = snapshot.val();

		if (
			dataCurrentACK !== undefined &&
			dataCurrentACK != "" &&
			dataCurrentACK !== null
		) {
			const idMessage = dataCurrentACK.idMessage,
				timestamp = dataCurrentACK.timestamp,
				type = dataCurrentACK.type;

			if (lastAlias == 'CHT') {
				let elemIconACK = $(".chatContentWrap-iconACK[data-idMessage='" + idMessage + "']");

				if (elemIconACK.length > 0) {
					let newIconACK = '',
						dateTimeStr = moment.unix(timestamp).tz(timezoneOffset).format('DD MMM YYYY HH:mm');

					switch (type) {
						case 'read': newIconACK = 'ri-check-double-line text-primary'; break;
						case 'delivered': newIconACK = 'ri-check-double-line text-muted'; break;
						case 'sent': newIconACK = 'ri-check-line text-muted'; break;
						default: newIconACK = 'ri-hourglass-2-fill text-muted'; break;
					}
					elemIconACK.removeClass('ri-hourglass-2-fill ri-check-line ri-check-double-line text-muted text-primary').addClass(newIconACK).parent().attr('data-ack-' + type, dateTimeStr);
				}
			}
		}
	});
</script>