<footer class="footer-custom mt-5" style="display: none !important;">
	<div class="container-fluid">
		<div class="row align-items-center py-2">
			<div class="col-md-4 text-start">
				<button class="btn btn-sm btn-outline-secondary" id="warningContainer" onclick="displayMiniWin()" title="Reminder System">
					<i class="bi bi-bell"></i> <span class="d-none d-md-inline">Reminder System</span>
				</button>
			</div>
			<div class="col-md-4 text-center">
				<small>&copy; <?php echo date('Y'); ?> Nangkoel 2008 OneClickSolution</small>
			</div>
			<div class="col-md-4 text-end">
				<button class="btn btn-sm btn-outline-secondary" id="chatContainer" onclick="chatPop()" title="Chat">
					<i class="bi bi-chat-dots"></i> <span class="d-none d-md-inline">CHAT</span>
				</button>
			</div>
		</div>
	</div>
</footer>

<!-- Chat Window -->
<div id="chatWindow" class="chat-window" style="display: none;">
	<div class="card">
		<div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
			<h6 class="mb-0">
				<i class="bi bi-chat-dots"></i> Chat<span id="chatWindowTitle"></span>
			</h6>
			<button type="button" class="btn-close btn-close-white" onclick="chatPop()" aria-label="Close"></button>
		</div>
		<div class="card-body p-2">
			<input id="chatWindowSearch" class="form-control form-control-sm mb-2" placeholder="Search contacts...">
			<div id="chatWindowContact" class="overflow-auto" style="max-height: 250px;"></div>
		</div>
	</div>
</div>

<style>
.chat-window {
	position: fixed;
	right: 20px;
	bottom: 70px;
	width: 280px;
	max-height: 350px;
	z-index: 10000;
	box-shadow: 0 5px 25px rgba(0,0,0,0.2);
	border-radius: 10px;
	overflow: hidden;
}

.chat-window .card {
	margin: 0;
	border-radius: 10px;
}

.footer-custom {
	border-top: 1px solid #9D9D9D;
	background-color: #EFEFEF;
	color: #ADADAD;
	position: fixed;
	bottom: 0;
	left: 0;
	right: 0;
	z-index: 1020;
}

.footer-custom .btn {
	font-size: 0.875rem;
}

/* Responsive adjustments */
@media (max-width: 768px) {
	.chat-window {
		width: 90%;
		right: 5%;
		bottom: 80px;
	}
}
</style>
