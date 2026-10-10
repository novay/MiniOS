//#region packages/novay/minios/resources/js/core/dock.js
function e() {
	return {
		dockDrag: null,
		dockDrop: null,
		dockDragSuppressedId: null,
		dockRevealOverride: !1,
		dockHovered: !1,
		contextMenu: {
			open: !1,
			type: "desktop",
			appId: null,
			x: 0,
			y: 0
		},
		openContextMenu(e) {
			this.closeAll();
			let t = e.clientX, n = e.clientY;
			t + 230 > window.innerWidth && (t = window.innerWidth - 230 - 8), n + 260 > window.innerHeight && (n = window.innerHeight - 260 - 8), this.contextMenu = {
				open: !0,
				type: "desktop",
				appId: null,
				x: Math.max(8, t),
				y: Math.max(36, n)
			};
		},
		openDockContextMenu(e, t) {
			this.closeAll();
			let n = this.isWindowRunning(t) ? 195 : 105, r = e.clientX, i = e.clientY, a = this.settings?.dock?.position ?? "bottom";
			a === "bottom" ? (i = e.clientY - n - 12, r = e.clientX - 105) : a === "left" ? (r = e.clientX + 14, i = e.clientY - n / 2) : a === "right" && (r = e.clientX - 210 - 14, i = e.clientY - n / 2), r + 210 > window.innerWidth && (r = window.innerWidth - 210 - 8), i + n > window.innerHeight && (i = window.innerHeight - n - 8), this.contextMenu = {
				open: !0,
				type: "dock",
				appId: t,
				x: Math.max(8, r),
				y: Math.max(36, i)
			};
		},
		openLauncherContextMenu(e, t) {
			this.closeContextMenu(), this.systemMenuOpen = !1;
			let n = e.clientX, r = e.clientY;
			n + 200 > window.innerWidth && (n = window.innerWidth - 200 - 8), r + 110 > window.innerHeight && (r = window.innerHeight - 110 - 8), this.contextMenu = {
				open: !0,
				type: "launcher",
				appId: t,
				x: Math.max(8, n),
				y: Math.max(36, r)
			};
		},
		openTrashContextMenu(e) {
			this.closeAll();
			let t = e.clientX, n = e.clientY;
			t + 220 > window.innerWidth && (t = window.innerWidth - 220 - 8), n + 150 > window.innerHeight && (n = window.innerHeight - 150 - 8), this.contextMenu = {
				open: !0,
				type: "trash",
				appId: null,
				x: Math.max(8, t),
				y: Math.max(36, n)
			};
		},
		closeContextMenu() {
			this.contextMenu.open = !1, this.contextMenu.appId = null;
		},
		getPinnedAppIds() {
			return this.settings?.dock?.pinned_apps && Array.isArray(this.settings.dock.pinned_apps) && this.settings.dock.pinned_apps.length > 0 ? [...this.settings.dock.pinned_apps] : Object.keys(this.applications).filter((e) => !!this.applications[e]?.pinned);
		},
		getDockAppIds() {
			let e = this.getPinnedAppIds(), t = Object.keys(this.windows || {}).filter((t) => this.isWindowRunning(t) && !e.includes(t) && !!this.applications[t]);
			return [...e, ...t];
		},
		getDockAppOrder(e) {
			let t = this.getDockAppIds().indexOf(e);
			return t === -1 ? 999 : t;
		},
		isAppPinned(e) {
			return this.getPinnedAppIds().includes(e);
		},
		isAppInDock(e) {
			return this.isAppPinned(e) || this.isWindowRunning(e);
		},
		isDockDragging(e = null) {
			return this.dockDrag?.active ? !e || this.dockDrag.appId === e : this.dockDrop ? !e || this.dockDrop.appId === e : !1;
		},
		isDockDragEnabled() {
			let e = this.settings?.dock?.enable_drag;
			return e !== !1 && e !== "false" && e !== 0 && e !== "0";
		},
		isDockClickSuppressed(e) {
			return this.dockDragSuppressedId === e;
		},
		measureDockItems() {
			let e = document.querySelector("[data-desktop-dock] nav");
			if (!e) return [];
			let t = Array.from(e.querySelectorAll("[data-dock-wrapper]")).filter((e) => {
				let t = e.getAttribute("data-dock-wrapper");
				return t && this.isAppInDock(t);
			});
			return t.sort((e, t) => {
				let n = e.getAttribute("data-dock-wrapper"), r = t.getAttribute("data-dock-wrapper");
				return this.getDockAppOrder(n) - this.getDockAppOrder(r);
			}), t.map((e) => {
				let t = e.getAttribute("data-dock-wrapper"), n = e.getBoundingClientRect();
				return {
					id: t,
					rect: n,
					centerX: n.left + n.width / 2,
					centerY: n.top + n.height / 2,
					width: n.width,
					height: n.height
				};
			});
		},
		startDockDrag(e, t) {
			if (!this.isDockDragEnabled() || e.button !== 0 && e.pointerType === "mouse") return;
			this.closeContextMenu(), this.dockDrop = null;
			let n = (this.settings?.dock?.position ?? "bottom") === "bottom", r = this.measureDockItems(), i = r.findIndex((e) => e.id === t);
			if (i === -1) return;
			let a = 48;
			if (r.length > 1) {
				let e = r[0], t = r[r.length - 1];
				a = n ? Math.abs(t.centerX - e.centerX) / (r.length - 1) : Math.abs(t.centerY - e.centerY) / (r.length - 1);
			} else r.length === 1 && (a = n ? r[0].width : r[0].height);
			(!a || a <= 0) && (a = 48), this.dockDrag = {
				active: !1,
				hasMoved: !1,
				appId: t,
				startX: e.clientX,
				startY: e.clientY,
				currentX: e.clientX,
				currentY: e.clientY,
				deltaX: 0,
				deltaY: 0,
				initialIndex: i,
				targetIndex: i,
				slotSize: a,
				isHorizontal: n,
				visibleItems: r,
				pointerId: e.pointerId,
				targetElement: e.currentTarget
			};
			try {
				e.currentTarget && typeof e.currentTarget.setPointerCapture == "function" && e.currentTarget.setPointerCapture(e.pointerId);
			} catch {}
		},
		handleDockDragMove(e) {
			if (!this.dockDrag || !this.isDockDragEnabled()) return;
			let t = e.clientX - this.dockDrag.startX, n = e.clientY - this.dockDrag.startY;
			if (!this.dockDrag.hasMoved) {
				if (Math.hypot(t, n) >= 4) this.dockDrag.hasMoved = !0, this.dockDrag.active = !0, this.dockDragSuppressedId = this.dockDrag.appId, document.body.style.userSelect = "none", document.body.style.cursor = "grabbing";
				else return;
			}
			if (!this.dockDrag.active) return;
			this.dockDrag.currentX = e.clientX, this.dockDrag.currentY = e.clientY, this.dockDrag.isHorizontal ? (this.dockDrag.deltaX = t, this.dockDrag.deltaY = Math.max(-14, Math.min(14, n))) : (this.dockDrag.deltaX = Math.max(-14, Math.min(14, t)), this.dockDrag.deltaY = n);
			let r = this.dockDrag.visibleItems;
			if (r.length > 0) {
				let t = this.dockDrag.initialIndex, n = Infinity, i = this.dockDrag.isHorizontal ? e.clientX : e.clientY;
				r.forEach((e, r) => {
					let a = this.dockDrag.isHorizontal ? e.centerX : e.centerY, o = Math.abs(i - a);
					o < n && (n = o, t = r);
				}), this.dockDrag.targetIndex = Math.max(0, Math.min(r.length - 1, t));
			}
		},
		handleDockDragEnd(e) {
			if (!this.dockDrag) return;
			let t = this.dockDrag;
			try {
				let n = t.targetElement || e?.currentTarget;
				n && typeof n.releasePointerCapture == "function" && t.pointerId !== void 0 && n.releasePointerCapture(t.pointerId);
			} catch {}
			if (document.body.style.userSelect = "", document.body.style.cursor = "", t.hasMoved && t.active) {
				this.dockDragSuppressedId = t.appId;
				let e = t.targetIndex !== t.initialIndex && t.visibleItems.length > 0, n = 0, r = 0;
				if (e) {
					let e = this.getDockAppIds(), i = t.appId, a = t.visibleItems[t.targetIndex];
					if (a && a.id !== i) {
						let n = e.indexOf(i);
						if (n !== -1) {
							e.splice(n, 1);
							let r = e.indexOf(a.id);
							r === -1 ? e.push(i) : t.targetIndex > t.initialIndex ? e.splice(r + 1, 0, i) : e.splice(r, 0, i), this.settings ||= {}, this.settings.dock || (this.settings.dock = {}), this.settings.dock.pinned_apps = [...e], this.applications[i] && (this.applications[i].pinned = !0), this.persistDockPinnedApps(this.settings.dock.pinned_apps);
						}
					}
					let o = t.visibleItems[t.initialIndex], s = t.visibleItems[t.targetIndex], c = s && o ? s.centerX - o.centerX : 0, l = s && o ? s.centerY - o.centerY : 0;
					n = t.deltaX - c, r = t.deltaY - l;
				} else n = t.deltaX, r = t.deltaY;
				this.dockDrop = {
					appId: t.appId,
					offsetX: n,
					offsetY: r,
					animating: !1
				}, this.dockDrag = null, requestAnimationFrame(() => {
					this.dockDrop && (this.dockDrop.animating = !0);
				}), setTimeout(() => {
					this.dockDrop = null, this.dockDragSuppressedId = null;
				}, 240);
			} else this.dockDrag = null, this.dockDrop = null, setTimeout(() => {
				this.dockDragSuppressedId = null;
			}, 100);
		},
		getDockItemStyle(e) {
			if (!this.isAppInDock(e)) return "display: none !important;";
			let t = `order: ${this.getDockAppOrder(e)};`;
			if (this.dockDrag && this.dockDrag.active) {
				let n = this.dockDrag;
				if (n.appId === e) return `${t} transform: translate3d(${n.deltaX}px, ${n.deltaY}px, 0) scale(1.08); z-index: 60; opacity: 0.95; filter: drop-shadow(0 10px 18px rgba(0,0,0,0.3)); transition: none; pointer-events: none;`;
				let r = n.visibleItems.findIndex((t) => t.id === e);
				if (r === -1) return t;
				let i = 0, a = n.initialIndex, o = n.targetIndex, s = n.slotSize;
				return o > a ? r > a && r <= o && (i = -s) : o < a && r >= o && r < a && (i = s), i === 0 ? `${t} transform: translate3d(0, 0, 0); transition: transform 0.22s cubic-bezier(0.2, 0, 0, 1);` : `${t} transform: translate3d(${n.isHorizontal ? i : 0}px, ${n.isHorizontal ? 0 : i}px, 0); transition: transform 0.22s cubic-bezier(0.2, 0, 0, 1);`;
			}
			return this.dockDrop ? this.dockDrop.appId === e ? this.dockDrop.animating ? `${t} transform: translate3d(0, 0, 0) scale(1); z-index: 60; opacity: 1; filter: drop-shadow(0 4px 6px rgba(0,0,0,0.1)); transition: transform 0.22s cubic-bezier(0.2, 0, 0, 1), scale 0.22s cubic-bezier(0.2, 0, 0, 1), filter 0.22s ease-out; pointer-events: none;` : `${t} transform: translate3d(${this.dockDrop.offsetX}px, ${this.dockDrop.offsetY}px, 0) scale(1.08); z-index: 60; opacity: 0.98; transition: none; pointer-events: none;` : `${t} transform: translate3d(0, 0, 0); transition: none !important;` : `${t} transform: translate3d(0, 0, 0); transition: none;`;
		},
		pinApp(e) {
			this.settings ||= {}, this.settings.dock || (this.settings.dock = {});
			let t = this.getPinnedAppIds();
			t.includes(e) || t.push(e), this.settings.dock.pinned_apps = t, this.applications[e] && (this.applications[e].pinned = !0), this.persistDockPinnedApps(t);
		},
		unpinApp(e) {
			this.settings ||= {}, this.settings.dock || (this.settings.dock = {});
			let t = this.getPinnedAppIds().filter((t) => t !== e);
			this.settings.dock.pinned_apps = t, this.applications[e] && (this.applications[e].pinned = !1), this.persistDockPinnedApps(t);
		},
		persistDockPinnedApps(e) {
			try {
				localStorage.setItem("minios:dock:pinned_apps", JSON.stringify(e));
			} catch {}
			window.Livewire && Livewire.dispatch("update-dock-setting", {
				key: "pinned_apps",
				value: e
			});
		},
		shouldExpandWorkspace() {
			if (!this.activeWindow) return !1;
			let e = this.getWindow(this.activeWindow);
			return !!(e && e.open && !e.minimized && e.maximized);
		},
		shouldHideDock() {
			return this.dockHovered || this.applicationsOpen || this.activitiesOpen || this.dockDrag?.active || this.dockDrop ? !1 : this.settings?.dock?.autohide ? !0 : this.shouldExpandWorkspace() && !this.dockRevealOverride;
		},
		getWorkspaceStyles() {
			let e = this.settings?.dock?.size || "medium", t = typeof e == "number" ? e : {
				small: 44,
				medium: 56,
				large: 68
			}[e] || parseInt(e, 10) || 56, n = this.settings?.dock?.position || "bottom", r = this.shouldHideDock(), i = "0px", a = "0px", o = "0px";
			return r || (n === "left" ? i = `${t}px` : n === "right" ? a = `${t}px` : n === "bottom" && (o = `${t}px`)), `left: ${i}; right: ${a}; bottom: ${o}; top: 28px; z-index: 0; isolation: isolate;`;
		},
		async animateWindowToDock(e) {
			let t = document.querySelector(`[data-window-id="${e}"]`);
			if (!t) return;
			let n = document.querySelector(`[data-dock-app="${e}"]`);
			if (!n) {
				let e = t.animate([{
					transform: "scale(1)",
					opacity: 1
				}, {
					transform: "scale(0.8)",
					opacity: 0
				}], {
					duration: 220,
					easing: "cubic-bezier(0.4, 0, 1, 1)",
					fill: "forwards"
				});
				try {
					await e.finished;
				} catch {}
				e.cancel();
				return;
			}
			let r = t.getBoundingClientRect(), i = n.getBoundingClientRect(), a = (document.querySelector("[data-desktop-dock]")?.getBoundingClientRect().width ?? 64) / 2, o = i.top + i.height / 2, s = r.left + r.width / 2, c = r.top + r.height / 2, l = a - s, u = o - c, d = Math.max(.04, i.width / r.width), f = Math.max(.04, i.height / r.height), p = t.animate([{
				transform: "translate(0px, 0px) scale(1, 1)",
				opacity: 1,
				filter: "blur(0px)"
			}, {
				transform: `translate(${l}px, ${u}px) scale(${d}, ${f})`,
				opacity: .15,
				filter: "blur(1px)"
			}], {
				duration: 300,
				easing: "cubic-bezier(0.4, 0, 0.2, 1)",
				fill: "forwards"
			});
			try {
				await p.finished;
			} catch {}
			return p;
		}
	};
}
//#endregion
//#region packages/novay/minios/resources/js/core/notifications.js
function t() {
	return {
		notifications: [],
		activeToasts: [],
		toastGroupHovered: !1,
		toastTicker: null,
		notificationCenterOpen: !1,
		audioContext: null,
		audioUnlocked: !1,
		audioDropdownOpen: !1,
		globalVolume: .8,
		globalMuted: !1,
		volumeHudVisible: !1,
		volumeHudTimer: null,
		recentNotifIds: null,
		get unreadNotificationsCount() {
			return this.notifications.filter((e) => !e.read).length;
		},
		get isAudioActive() {
			return this.settings?.notifications?.sound !== !1 && this.audioUnlocked;
		},
		initNotificationListener() {
			this.recentNotifIds = /* @__PURE__ */ new Set();
			let e = (e, t = "info") => {
				if (!e) return;
				let n = Array.isArray(e) ? e[0] : e;
				if (!n) return;
				let r = typeof n == "string" ? {
					message: n,
					variant: t
				} : { ...n };
				!r.variant && t !== "info" && (r.variant = t);
				let i = r.id || (r.message ? "msg_" + r.message + "_" + (r.variant || "") : null);
				if (i) {
					if (this.recentNotifIds.has(i)) return;
					this.recentNotifIds.add(i), setTimeout(() => {
						this.recentNotifIds.delete(i);
					}, 800);
				}
				this.handleOsNotify(r);
			};
			window.addEventListener("os-notify", (t) => e(t.detail)), window.addEventListener("toast", (t) => e(t.detail)), window.addEventListener("toast-success", (t) => e(t.detail, "success")), window.addEventListener("toast-error", (t) => e(t.detail, "danger")), window.addEventListener("toast-warning", (t) => e(t.detail, "warning")), window.addEventListener("toast-info", (t) => e(t.detail, "info")), window.Livewire && (Livewire.on("os-notify", (t) => e(t)), Livewire.on("toast", (t) => e(t)), Livewire.on("toast-success", (t) => e(t, "success")), Livewire.on("toast-error", (t) => e(t, "danger")), Livewire.on("toast-warning", (t) => e(t, "warning")), Livewire.on("toast-info", (t) => e(t, "info")));
		},
		handleOsNotify(e) {
			let t = Array.isArray(e) ? e[0] : e;
			if (!t) return;
			let n = (t.app_id || t.appId || t.app || "").toLowerCase(), r = this.applications[n] || Object.values(this.applications).find((e) => e.name?.toLowerCase() === n), i = t.icon || r?.icon || (this.applications[n] ? this.applications[n].icon : null), a = {
				id: t.id || "notif_" + Date.now() + "_" + Math.random().toString(36).substr(2, 5),
				title: t.title || "",
				text: t.text || t.message || "",
				variant: t.variant || t.type || "info",
				app: t.app || r?.name || "MiniOS",
				appId: r?.id || n || "minios",
				icon: i,
				icon_url: t.icon_url || null,
				timestamp: t.timestamp || (/* @__PURE__ */ new Date()).toISOString(),
				time: (/* @__PURE__ */ new Date()).toLocaleTimeString([], {
					hour: "2-digit",
					minute: "2-digit"
				}),
				read: !1
			};
			this.notifications.unshift(a), this.notifications.length > 50 && (this.notifications = this.notifications.slice(0, 50)), t.show_toast !== !1 && this.showToast(a), this.playNotificationChime();
		},
		showToast(e) {
			let t = e.duration || 5e3, n = {
				...e,
				duration: t,
				remaining: t,
				progress: 100,
				paused: !1,
				visible: !0,
				leaving: !1,
				entering: !0
			}, r = this.activeToasts.filter((e) => !e.leaving);
			if (r.length >= 3) {
				let e = r[0];
				e && this.dismissToast(e.id);
			}
			this.activeToasts.push(n), setTimeout(() => {
				n.entering = !1;
			}, 200), this.ensureToastTicker();
		},
		ensureToastTicker() {
			this.toastTicker ||= setInterval(() => {
				if (this.activeToasts.length === 0) {
					clearInterval(this.toastTicker), this.toastTicker = null;
					return;
				}
				this.activeToasts.forEach((e) => {
					e.leaving || e.paused || this.toastGroupHovered || (e.remaining -= 50, e.duration > 0 && (e.progress = Math.max(0, e.remaining / e.duration * 100)), e.remaining <= 0 && this.dismissToast(e.id));
				});
			}, 50);
		},
		dismissToast(e) {
			let t = this.activeToasts.find((t) => t.id === e);
			!t || t.leaving || (t.leaving = !0, t.visible = !1, t.entering = !1, setTimeout(() => {
				let t = this.activeToasts.findIndex((t) => t.id === e);
				t !== -1 && this.activeToasts.splice(t, 1);
			}, 180));
		},
		pauseToast(e) {
			let t = this.activeToasts.find((t) => t.id === e);
			t && (t.paused = !0);
		},
		resumeToast(e) {
			let t = this.activeToasts.find((t) => t.id === e);
			t && (t.paused = !1, t.remaining < 2500 && (t.remaining = 2500, t.duration = Math.max(t.duration, 2500)));
		},
		pauseAllToasts() {
			this.toastGroupHovered = !0, this.activeToasts.forEach((e) => {
				e.paused = !0;
			});
		},
		resumeAllToasts() {
			this.toastGroupHovered = !1, this.activeToasts.forEach((e) => {
				e.paused = !1, e.remaining < 2500 && (e.remaining = 2500, e.duration = Math.max(e.duration, 2500));
			});
		},
		getToastStyle(e) {
			let t = this.settings?.notifications?.position || "bottom end", n = t.includes("start"), r = t.includes("top"), i = this.activeToasts.filter((e) => !e.leaving), a = i.indexOf(e), o = a === -1 ? 0 : Math.max(0, i.length - 1 - a), s = n ? "-125%" : "125%", c = e.leaving ? s : "0px";
			if (this.toastGroupHovered) return {
				transform: `translate3d(${c}, 0, 0) scale(1)`,
				opacity: +!e.leaving,
				zIndex: 50 - o,
				position: "relative",
				marginBottom: "0.625rem"
			};
			let l = r ? o * 12 : -o * 12, u = Math.max(.85, 1 - o * .05), d = e.leaving ? 0 : Math.max(.65, 1 - o * .16);
			return {
				transform: `translate3d(${c}, ${l}px, 0) scale(${u})`,
				opacity: d,
				zIndex: 50 - o,
				position: o === 0 ? "relative" : "absolute",
				bottom: r ? "auto" : "0",
				top: r ? "0" : "auto",
				right: n ? "auto" : "0",
				left: n ? "0" : "auto",
				marginBottom: "0px"
			};
		},
		getAudioContext() {
			if (!this.audioContext) {
				let e = window.AudioContext || window.webkitAudioContext;
				e && (this.audioContext = new e(), this.audioContext.onstatechange = () => {
					this.audioUnlocked = this.audioContext.state === "running";
				});
			}
			return this.audioContext;
		},
		checkAudioStatus() {
			let e = this.getAudioContext();
			e && (this.audioUnlocked = e.state === "running");
		},
		async toggleAudio() {
			let e = this.getAudioContext();
			if (e) {
				if (this.settings ||= {}, this.settings.notifications || (this.settings.notifications = {}), this.isAudioActive) {
					this.settings.notifications.sound = !1, this.audioUnlocked = !1;
					try {
						await e.suspend();
					} catch {}
				} else {
					this.settings.notifications.sound = !0;
					try {
						e.state === "suspended" && await e.resume(), this.audioUnlocked = e.state === "running";
					} catch {
						this.audioUnlocked = !1;
					}
					this.audioUnlocked && this.playNotificationChime();
				}
				window.Livewire && Livewire.dispatch("os-setting-updated", {
					category: "notifications",
					key: "sound",
					value: this.settings.notifications.sound
				});
			}
		},
		initAudioSystem() {
			this.checkAudioStatus();
			try {
				let e = localStorage.getItem("minios_global_volume");
				if (e !== null) {
					let t = parseFloat(e);
					isNaN(t) || (this.globalVolume = Math.max(0, Math.min(1, t)));
				}
				let t = localStorage.getItem("minios_global_muted");
				t !== null && (this.globalMuted = t === "true");
			} catch {}
			window.addEventListener("minios-set-volume", (e) => {
				if (e.detail) {
					if (typeof e.detail.volume == "number" || typeof e.detail.volume == "string") {
						let t = parseFloat(e.detail.volume);
						isNaN(t) || (this.globalVolume = Math.max(0, Math.min(1, t)));
					}
					typeof e.detail.muted == "boolean" ? this.globalMuted = e.detail.muted : this.globalVolume > 0 && this.globalMuted && (this.globalMuted = !1), this.saveGlobalVolume(), this.broadcastGlobalVolume(e.detail.source || null);
				}
			}), window.addEventListener("minios-request-volume", () => {
				this.broadcastGlobalVolume();
			});
			let e = () => {
				let e = this.getAudioContext();
				e && e.state === "suspended" && this.settings?.notifications?.sound !== !1 && e.resume().then(() => {
					this.audioUnlocked = e.state === "running";
				}).catch(() => {});
			};
			window.addEventListener("pointerdown", e, { once: !0 }), window.addEventListener("keydown", e, { once: !0 }), setTimeout(() => {
				this.broadcastGlobalVolume();
			}, 100);
		},
		toggleAudioDropdown() {
			let e = !this.audioDropdownOpen;
			typeof this.closeAll == "function" && this.closeAll(), this.audioDropdownOpen = e;
		},
		saveGlobalVolume() {
			try {
				localStorage.setItem("minios_global_volume", this.globalVolume.toString()), localStorage.setItem("minios_global_muted", this.globalMuted ? "true" : "false");
			} catch {}
		},
		setGlobalVolume(e, t = "system") {
			let n = parseFloat(e);
			isNaN(n) || (this.globalVolume = Math.max(0, Math.min(1, Math.round(n * 100) / 100)), this.globalVolume > 0 && this.globalMuted ? this.globalMuted = !1 : this.globalVolume === 0 && (this.globalMuted = !0), this.saveGlobalVolume(), this.broadcastGlobalVolume(t));
		},
		toggleGlobalMute(e = "system") {
			this.globalMuted = !this.globalMuted, this.saveGlobalVolume(), this.broadcastGlobalVolume(e);
		},
		increaseGlobalVolume(e = .05) {
			let t = Math.min(1, Math.round((this.globalVolume + e) * 100) / 100);
			this.globalMuted = !1, this.setGlobalVolume(t), this.showVolumeHud();
		},
		decreaseGlobalVolume(e = .05) {
			let t = Math.max(0, Math.round((this.globalVolume - e) * 100) / 100);
			t === 0 && (this.globalMuted = !0), this.setGlobalVolume(t), this.showVolumeHud();
		},
		showVolumeHud() {
			this.volumeHudVisible = !0, this.volumeHudTimer && clearTimeout(this.volumeHudTimer), this.volumeHudTimer = setTimeout(() => {
				this.volumeHudVisible = !1;
			}, 1500);
		},
		broadcastGlobalVolume(e = null) {
			let t = this.globalMuted ? 0 : this.globalVolume;
			document.querySelectorAll("audio, video").forEach((e) => {
				try {
					e.volume = t;
				} catch {}
			}), window.dispatchEvent(new CustomEvent("minios-volume-changed", { detail: {
				volume: this.globalVolume,
				muted: this.globalMuted,
				effectiveVolume: t,
				source: e
			} }));
		},
		playNotificationChime() {
			try {
				if (this.settings?.notifications?.sound === !1) return;
				let e = this.globalMuted ? 0 : this.globalVolume;
				if (e <= 0) return;
				let t = this.getAudioContext();
				if (!t) return;
				t.state === "suspended" ? t.resume().then(() => {
					this.audioUnlocked = t.state === "running";
				}).catch(() => {}) : t.state === "running" && (this.audioUnlocked = !0);
				let n = t.currentTime, r = (n, r, i) => {
					let a = t.createOscillator(), o = t.createGain();
					a.type = "sine", a.frequency.setValueAtTime(n, r), o.gain.setValueAtTime(0, r), o.gain.linearRampToValueAtTime(.12 * e, r + .02), o.gain.exponentialRampToValueAtTime(1e-4, r + i), a.connect(o), o.connect(t.destination), a.start(r), a.stop(r + i);
				};
				r(587.33, n, .18), r(880, n + .09, .28);
			} catch {}
		},
		toggleNotificationCenter() {
			let e = !this.notificationCenterOpen;
			typeof this.closeAll == "function" && this.closeAll(), this.notificationCenterOpen = e, e && this.notifications.forEach((e) => {
				e.read = !0;
			});
		},
		removeNotification(e) {
			this.notifications = this.notifications.filter((t) => t.id !== e);
		},
		clearAllNotifications() {
			this.notifications = [];
		}
	};
}
//#endregion
//#region packages/novay/minios/resources/js/core/router.js
function n() {
	return {
		activeApplication: null,
		currentPath: "/",
		currentUrl: "/",
		popstateHandler: null,
		initRouter() {
			this.syncRoute(), this.popstateHandler = () => {
				this.syncRoute();
			}, window.addEventListener("popstate", this.popstateHandler);
		},
		normalizePath(e) {
			if (!e) return "/";
			let t = e;
			return t.startsWith("/") || (t = `/${t}`), t.length > 1 && t.endsWith("/") && (t = t.slice(0, -1)), t;
		},
		desktopPath(e = "") {
			let t = e ? "/" + e.replace(/^\/+/, "") : "";
			return (this.basePath || "/").replace(/\/+$/, "") + t || "/";
		},
		resolveApplication(e) {
			let t = this.normalizePath(e), n = [];
			return Object.entries(this.applications).forEach(([e, r]) => {
				(r.routes ?? []).forEach((i) => {
					let a = this.normalizePath(i), o = t === a, s = t.startsWith(`${a}/`);
					(o || s) && n.push({
						id: e,
						application: r,
						route: a
					});
				});
			}), n.sort((e, t) => t.route.length - e.route.length), n[0] ?? null;
		},
		syncRoute() {
			let e = this.normalizePath(window.location.pathname);
			this.currentPath = e, this.currentUrl = e + window.location.search + window.location.hash;
			let t = this.resolveApplication(e);
			if (!t) {
				this.activeApplication = null, this.activeWindow = null;
				return;
			}
			let n = e.slice(t.route.length);
			n ||= "/", this.activeApplication = {
				id: t.id,
				name: t.application.name,
				icon: t.application.icon,
				entry: t.application.entry,
				baseRoute: t.route,
				path: e,
				subPath: n,
				config: t.application
			}, this.openWindow(t.id, {
				url: this.currentUrl,
				focus: !1
			});
			let r = this.getWindow(t.id);
			r && (r.open = !0, r.minimized = !1, r.zIndex = this.nextWindowZIndex(), this.activeWindow = t.id);
		},
		navigate(e, t = {}) {
			let { replace: n = !1 } = t, r = new URL(e, window.location.origin), i = this.normalizePath(r.pathname) + r.search + r.hash;
			if (i === this.currentUrl) {
				this.syncRoute(), this.closeAll();
				return;
			}
			n ? window.history.replaceState({}, "", i) : window.history.pushState({}, "", i), this.syncRoute(), this.closeAll();
		}
	};
}
//#endregion
//#region packages/novay/minios/resources/js/core/session.js
function r() {
	return {
		windowSessionKey: "web-desktop:window-session:v2",
		persistTimer: null,
		pagehideHandler: null,
		visibilityChangeHandler: null,
		scheduleWindowSessionSave() {
			this.persistTimer && clearTimeout(this.persistTimer), this.persistTimer = setTimeout(() => {
				this.flushWindowSession();
			}, 100);
		},
		flushWindowSession() {
			this.persistTimer !== null && clearTimeout(this.persistTimer), this.persistTimer = null, this.saveWindowSession();
		},
		saveWindowSession() {
			let e = {};
			Object.entries(this.windows).forEach(([t, n]) => {
				e[t] = {
					open: n.open,
					minimized: n.minimized,
					maximized: n.maximized,
					x: n.x,
					y: n.y,
					width: n.width,
					height: n.height,
					zIndex: n.zIndex,
					url: n.url,
					restore: n.restore
				};
			});
			let t = {
				version: 2,
				windows: e,
				activeWindow: this.activeWindow,
				zIndexCounter: this.zIndexCounter
			};
			try {
				localStorage.setItem(this.windowSessionKey, JSON.stringify(t));
			} catch (e) {
				console.warn("Unable to persist desktop session.", e);
			}
		},
		restoreWindowSession() {
			let e;
			try {
				let t = localStorage.getItem(this.windowSessionKey) ?? localStorage.getItem("web-desktop:window-session:v1");
				if (!t) return;
				e = JSON.parse(t);
			} catch (e) {
				console.warn("Unable to restore desktop session.", e);
				return;
			}
			let t = (e) => typeof e == "object" && !!e && !Array.isArray(e), n = (e) => typeof e == "number" && Number.isFinite(e);
			if (!t(e) || !t(e.windows)) return;
			let r = this.getWorkspaceRect();
			for (let [i, a] of Object.entries(e.windows)) {
				if (!Object.hasOwn(this.windows, i) || !t(a)) continue;
				let e = this.windows[i];
				for (let t of [
					"x",
					"y",
					"width",
					"height"
				]) n(a[t]) && (e[t] = a[t]);
				if (this.fitWindowGeometry(e, r), e.open = a.open === !0, e.minimized = e.open && a.minimized === !0, e.maximized = e.maximizable !== !1 && a.maximized === !0, e.resizable === !1) {
					let t = this.applications[i]?.window ?? {};
					typeof t.width == "number" && (e.width = t.width), typeof t.height == "number" && (e.height = t.height);
				}
				e.url = this.resolveWindowUrl(i, a.url), e.zIndex = n(a.zIndex) ? a.zIndex : e.zIndex, e.restore = null, t(a.restore) && [
					"x",
					"y",
					"width",
					"height"
				].every((e) => n(a.restore[e])) && a.restore.width > 0 && a.restore.height > 0 && (e.restore = {
					x: a.restore.x,
					y: a.restore.y,
					width: a.restore.width,
					height: a.restore.height
				}, this.fitWindowGeometry(e.restore, r, e));
			}
			this.normalizeWindowStack(), this.activeWindow = typeof e.activeWindow == "string" && this.isWindowVisible(e.activeWindow) ? e.activeWindow : null;
		},
		resolveWindowUrl(e, t) {
			let n = this.applications[e], r = n.entry ?? n.routes?.[0] ?? "/";
			if (typeof t != "string" || !t.trim()) return r;
			try {
				let n = new URL(t, window.location.origin);
				return n.origin !== window.location.origin || this.resolveApplication(n.pathname)?.id !== e ? r : this.normalizePath(n.pathname) + n.search + n.hash;
			} catch {
				return r;
			}
		},
		fitWindowGeometry(e, t, n = e) {
			e.width = this.clamp(e.width, Math.min(n.minWidth ?? 300, t.width), t.width), e.height = this.clamp(e.height, Math.min(n.minHeight ?? 220, t.height), t.height), e.x = this.clamp(e.x, 0, Math.max(0, t.width - e.width)), e.y = this.clamp(e.y, 0, Math.max(0, t.height - 40));
		},
		normalizeWindowStack() {
			let e = Object.values(this.windows).sort((e, t) => e.zIndex - t.zIndex);
			this.zIndexCounter = 100;
			for (let t of e) t.zIndex = ++this.zIndexCounter;
		},
		nextWindowZIndex() {
			return (!Number.isFinite(this.zIndexCounter) || this.zIndexCounter >= 8e3) && this.normalizeWindowStack(), ++this.zIndexCounter;
		}
	};
}
//#endregion
//#region packages/novay/minios/resources/js/core/shortcuts.js
function i() {
	return {
		keydownHandler: null,
		initKeyboardShortcuts() {
			this.keydownHandler = (e) => {
				let t = e.code === "Space" || e.key === " ", n = e.key === "k" || e.key === "K", r = e.key === "s" || e.key === "S", i = e.metaKey || e.ctrlKey, a = e.key === "F12" || e.code === "F12", o = e.key === "F11" || e.code === "F11";
				if (i && a) {
					e.preventDefault(), e.stopPropagation(), this.increaseGlobalVolume(.05);
					return;
				}
				if (i && o) {
					e.preventDefault(), e.stopPropagation(), this.decreaseGlobalVolume(.05);
					return;
				}
				let s = i && !e.altKey && !e.shiftKey && (t || n), c = e.metaKey && !e.ctrlKey && !e.altKey && !e.shiftKey && r;
				if (s || c) {
					e.preventDefault(), e.stopPropagation(), this.toggleApplications();
					return;
				}
				if (!((e.metaKey || e.ctrlKey || e.altKey) && !e.shiftKey && (e.key === "w" || e.key === "W" || e.code === "KeyW"))) return;
				let l = Object.values(this.windows).some((e) => e.open), u = this.normalizePath(window.location.pathname) !== "/";
				if (l || u || this.applicationsOpen || this.systemMenuOpen) {
					if (e.preventDefault(), e.stopPropagation(), this.applicationsOpen) {
						this.applicationsOpen = !1;
						return;
					}
					if (this.systemMenuOpen) {
						this.systemMenuOpen = !1;
						return;
					}
					this.closeActiveOrTopWindow();
				}
			}, window.addEventListener("keydown", this.keydownHandler, !0);
		},
		closeActiveOrTopWindow() {
			if (this.activeWindow && this.windows[this.activeWindow]?.open) {
				this.closeWindow(this.activeWindow);
				return;
			}
			let e = this.getTopVisibleWindow();
			if (e) {
				this.activeWindow = e.id, this.closeWindow(e.id);
				return;
			}
			let t = Object.values(this.windows).find((e) => e.open);
			if (t) {
				this.activeWindow = t.id, this.closeWindow(t.id);
				return;
			}
			let n = this.desktopPath ? this.desktopPath() : "/";
			this.normalizePath(window.location.pathname) !== this.normalizePath(n) && this.navigate(n, { replace: !0 });
		}
	};
}
//#endregion
//#region packages/novay/minios/resources/js/core/system-ui.js
function a() {
	return {
		clock: "",
		clockTimer: null,
		activitiesOpen: !1,
		applicationsOpen: !1,
		systemMenuOpen: !1,
		aboutOpen: !1,
		selectedShortcut: null,
		isFullscreen: !1,
		get isDarkMode() {
			let e = this.settings?.appearance?.theme || "system";
			return e === "dark" || e === "system" && window.matchMedia("(prefers-color-scheme: dark)").matches;
		},
		initClock() {
			this.updateClock(), this.clockTimer = setInterval(() => {
				this.updateClock();
			}, 1e3);
		},
		updateClock() {
			let e = /* @__PURE__ */ new Date(), t = this.settings?.locale_time?.time_format === "12h", n = this.settings?.locale_time?.locale === "id" ? "id-ID" : "en-US", r = this.settings?.locale_time?.timezone || void 0, i = "", a = "";
			try {
				i = new Intl.DateTimeFormat(n, {
					weekday: "short",
					timeZone: r
				}).format(e), a = new Intl.DateTimeFormat(n, {
					hour: "2-digit",
					minute: "2-digit",
					hour12: t,
					timeZone: r
				}).format(e);
			} catch {
				i = new Intl.DateTimeFormat("en-US", { weekday: "short" }).format(e), a = new Intl.DateTimeFormat("en-US", {
					hour: "2-digit",
					minute: "2-digit",
					hour12: t
				}).format(e);
			}
			this.clock = `${i} ${a}`;
		},
		initSettingsListener() {
			this.applyTheme(), window.Livewire && (Livewire.on("os-setting-updated", (e) => {
				let t = Array.isArray(e) ? e[0] : e;
				t && t.category && t.key && (this.settings[t.category] || (this.settings[t.category] = {}), this.settings[t.category][t.key] = t.value, this.applyTheme(), this.updateClock());
			}), Livewire.on("os-setting-reset", (e) => {
				let t = Array.isArray(e) ? e[0] : e;
				t && t.category && (this.applyTheme(), this.updateClock());
			}));
		},
		toggleTheme() {
			this.closeAll(), this.settings ||= {}, this.settings.appearance || (this.settings.appearance = {});
			let e = this.isDarkMode ? "light" : "dark";
			this.settings.appearance.theme = e, this.applyTheme(), window.Livewire && Livewire.dispatch("toggle-dark-mode");
		},
		applyTheme() {
			let e = this.settings?.appearance?.theme || "system";
			e === "dark" || e === "system" && window.matchMedia("(prefers-color-scheme: dark)").matches ? document.documentElement.classList.add("dark") : document.documentElement.classList.remove("dark");
			let t = this.settings?.appearance?.accent_color || "indigo";
			document.documentElement.setAttribute("data-accent", t);
			let n = {
				zinc: "#27272a",
				indigo: "#6366f1",
				emerald: "#10b981",
				sky: "#0ea5e9",
				amber: "#f59e0b",
				rose: "#f43f5e",
				violet: "#8b5cf6"
			}, r = n[t] || n.indigo;
			document.documentElement.style.setProperty("--accent-color", r), this.settings?.appearance?.panel_blur ?? !0 ? document.documentElement.classList.remove("no-blur") : document.documentElement.classList.add("no-blur");
			let i = this.settings?.appearance?.font_family || "inter";
			document.documentElement.setAttribute("data-font", i);
			let a = {
				ubuntu: "'Ubuntu', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif",
				segoe: "'Segoe UI', -apple-system, BlinkMacSystemFont, Roboto, sans-serif",
				"system-ui": "system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif",
				inter: "'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif",
				google: "'Google Sans', 'Product Sans', 'Roboto', -apple-system, BlinkMacSystemFont, sans-serif",
				"san-francisco": "-apple-system, BlinkMacSystemFont, 'SF Pro Display', 'SF Pro Text', 'Helvetica Neue', sans-serif"
			}, o = a[i] || a.inter;
			document.documentElement.style.setProperty("--font-sans", o), document.body && (document.body.style.fontFamily = o), this.applyWallpaper();
		},
		applyWallpaper() {
			let e = this.settings?.appearance?.wallpaper || "wall-1", t = document.querySelector(".desktop-wallpaper");
			if (t) {
				let n = e.endsWith(".webp") ? e : `${e}.webp`;
				t.style.backgroundImage = `url('/minios/wallpapers/${n}')`, t.style.backgroundSize = "cover", t.style.backgroundPosition = "center", t.style.backgroundRepeat = "no-repeat";
			}
		},
		toggleAboutModal() {
			let e = !this.aboutOpen;
			this.closeAll(), this.aboutOpen = e;
		},
		toggleApplications() {
			let e = !this.applicationsOpen;
			this.closeAll(), this.applicationsOpen = e;
		},
		toggleSystemMenu() {
			let e = !this.systemMenuOpen;
			this.closeAll(), this.systemMenuOpen = e;
		},
		toggleFullscreen() {
			document.fullscreenElement ? document.exitFullscreen && (document.exitFullscreen().then(() => {
				navigator.keyboard?.unlock && navigator.keyboard.unlock();
			}).catch(() => {}), this.isFullscreen = !1) : (document.documentElement.requestFullscreen && document.documentElement.requestFullscreen().then(() => {
				navigator.keyboard?.lock && navigator.keyboard.lock(["KeyW"]).catch(() => {});
			}).catch(() => {}), this.isFullscreen = !0);
		},
		closeAll() {
			this.activitiesOpen = !1, this.applicationsOpen = !1, this.systemMenuOpen = !1, this.notificationCenterOpen = !1, this.audioDropdownOpen = !1, this.aboutOpen = !1, this.selectedShortcut = null, typeof this.closeContextMenu == "function" && this.closeContextMenu();
		}
	};
}
//#endregion
//#region packages/novay/minios/resources/js/core/utils.js
function o(e, ...t) {
	for (let n of t) n && Object.defineProperties(e, Object.getOwnPropertyDescriptors(n));
	return e;
}
//#endregion
//#region packages/novay/minios/resources/js/core/window-manager.js
function s() {
	return {
		windows: {},
		activeWindow: null,
		dragState: null,
		resizeState: null,
		pointerMoveHandler: null,
		pointerUpHandler: null,
		viewportResizeHandler: null,
		bootstrapWindows() {
			let e = this.getWorkspaceRect(), t = {}, n = 0;
			Object.entries(this.applications).forEach(([r, i]) => {
				let a = i.window ?? {}, o = a.width ?? 900, s = a.height ?? 600, c = Math.min(o, Math.max(300, e.width - 40)), l = Math.min(s, Math.max(220, e.height - 40)), u = n % 8 * 28, d = Math.max(0, (e.width - c) / 2), f = Math.max(0, (e.height - l) / 2), p = a.center === !0 || r === "about", m = p ? d : this.clamp(d + u, 0, Math.max(0, e.width - c)), h = p ? f : this.clamp(f + u, 0, Math.max(0, e.height - l));
				t[r] = {
					id: r,
					title: i.name,
					icon: i.icon,
					open: !1,
					minimized: !1,
					maximized: !1,
					x: m,
					y: h,
					width: c,
					height: l,
					minWidth: a.min_width ?? 420,
					minHeight: a.min_height ?? 280,
					resizable: a.resizable !== !1,
					maximizable: a.maximizable !== !1,
					zIndex: ++this.zIndexCounter,
					url: i.entry ?? i.routes?.[0] ?? "/",
					restore: null
				}, n++;
			}), this.windows = t;
		},
		ensureWindow(e) {
			return this.windows[e] || (console.warn(`Desktop window [${e}] is not registered.`), null);
		},
		getWindow(e) {
			return Object.hasOwn(this.windows, e) ? this.windows[e] : null;
		},
		openWindow(e, { url: t = null, focus: n = !0 } = {}) {
			let r = this.ensureWindow(e);
			r && ((e === "about" || this.applications[e]?.window?.center) && this.centerWindow(e), r.open = !0, r.minimized = !1, t && (r.url = t), n && (r.zIndex = this.nextWindowZIndex(), this.activeWindow = e), this.scheduleWindowSessionSave());
		},
		centerWindow(e) {
			let t = this.getWindow(e);
			if (!t) return;
			let n = this.getWorkspaceRect();
			t.x = Math.max(0, (n.width - t.width) / 2), t.y = Math.max(0, (n.height - t.height) / 2);
		},
		openApplication(e, t = {}) {
			let n = this.applications[e];
			if (!n) {
				console.warn(`Desktop application [${e}] is not registered.`);
				return;
			}
			if ([
				"preview",
				"editor",
				"textedit",
				"player"
			].includes(e) && !t?.path && (this.applicationsOpen || t?.fromLauncher)) {
				this.applicationsOpen = !1, this.closeAll();
				let t = n.name || (e === "editor" ? "Editor" : e === "player" ? "Player" : "Preview");
				this.handleOsNotify({
					title: "",
					message: "Aplikasi berjalan, gunakan via Files.",
					text: "Aplikasi berjalan, gunakan via Files.",
					variant: "info",
					app: t
				});
				return;
			}
			(e === "about" || n.window?.center) && this.centerWindow(e);
			let r = this.getWindow(e);
			if (r && r.open) {
				r.minimized = !1, this.focusWindow(e, { syncUrl: !0 }), this.closeAll();
				return;
			}
			let i = n.entry ?? n.routes?.[0];
			if (!i) {
				console.warn(`Desktop application [${e}] has no entry route.`);
				return;
			}
			this.openWindow(e, {
				url: i,
				focus: !1
			}), this.navigate(i), this.closeAll();
		},
		focusWindow(e, { syncUrl: t = !0 } = {}) {
			let n = this.getWindow(e);
			if (!(!n || !n.open)) {
				if (n.minimized = !1, n.zIndex = this.nextWindowZIndex(), this.activeWindow = e, t && n.url && this.currentUrl !== n.url) {
					let e = new URL(n.url, window.location.origin), t = this.normalizePath(e.pathname) + e.search + e.hash;
					window.history.replaceState({}, "", t), this.currentPath = this.normalizePath(e.pathname), this.currentUrl = t;
					let r = this.resolveApplication(this.currentPath);
					if (r) {
						let e = this.currentPath.slice(r.route.length);
						e ||= "/", this.activeApplication = {
							id: r.id,
							name: r.application.name,
							icon: r.application.icon,
							entry: r.application.entry,
							baseRoute: r.route,
							path: this.currentPath,
							subPath: e,
							config: r.application
						};
					}
				}
				this.scheduleWindowSessionSave();
			}
		},
		getTopVisibleWindow(e = null) {
			let t = Object.values(this.windows).filter((t) => t.id !== e && t.open && !t.minimized);
			return t.sort((e, t) => t.zIndex - e.zIndex), t[0] ?? null;
		},
		closeWindow(e) {
			let t = this.getWindow(e);
			if (t) {
				if (t.open = !1, t.minimized = !1, window.dispatchEvent(new CustomEvent("window-closed", { detail: { id: e } })), window.dispatchEvent(new CustomEvent("minios:window-closed", { detail: { id: e } })), e === "soundcloud" && window.scWidget) try {
					window.scWidget.pause();
				} catch {}
				if (this.activeWindow === e) {
					this.activeWindow = null;
					let t = this.getTopVisibleWindow(e);
					t ? this.focusWindow(t.id, { syncUrl: !0 }) : this.navigate(this.desktopPath ? this.desktopPath() : "/", { replace: !0 });
				}
				this.scheduleWindowSessionSave();
			}
		},
		async minimizeWindow(e) {
			let t = this.getWindow(e);
			if (!t || t.minimized) return;
			t.maximized && (this.dockRevealOverride = !0);
			let n = await this.animateWindowToDock(e);
			if (t.minimized = !0, this.activeWindow === e) {
				this.activeWindow = null;
				let t = this.getTopVisibleWindow(e);
				t ? this.focusWindow(t.id, { syncUrl: !0 }) : this.navigate(this.desktopPath ? this.desktopPath() : "/", { replace: !0 });
			}
			this.dockRevealOverride = !1, this.scheduleWindowSessionSave(), n && requestAnimationFrame(() => {
				n.cancel();
			});
		},
		toggleMaximizeWindow(e) {
			let t = this.getWindow(e);
			if (!(!t || t.maximizable === !1)) {
				if (this.focusWindow(e, { syncUrl: !1 }), !t.maximized) {
					t.restore = {
						x: t.x,
						y: t.y,
						width: t.width,
						height: t.height
					}, t.maximized = !0, this.scheduleWindowSessionSave();
					return;
				}
				t.maximized = !1, t.restore && (t.x = t.restore.x, t.y = t.restore.y, t.width = t.restore.width, t.height = t.restore.height), t.restore = null, this.fitWindowGeometry(t, this.getWorkspaceRect()), this.scheduleWindowSessionSave();
			}
		},
		isWindowRunning(e) {
			return this.getWindow(e)?.open === !0;
		},
		isWindowMinimized(e) {
			let t = this.windows[e];
			return !!(t && t.open === !0 && t.minimized === !0);
		},
		isWindowVisible(e) {
			let t = this.windows[e];
			return !!(t && t.open === !0 && t.minimized === !1);
		},
		isWindowFocused(e) {
			return this.activeWindow === e && this.isWindowVisible(e);
		},
		isWindowMaximizable(e) {
			return this.getWindow(e)?.maximizable !== !1;
		},
		isWindowResizable(e) {
			return this.getWindow(e)?.resizable !== !1;
		},
		isWindowInteracting(e) {
			return this.dragState?.id === e || this.resizeState?.id === e;
		},
		windowStyle(e) {
			let t = this.getWindow(e);
			return t ? t.maximized ? `left: 0px; top: 0px; width: 100%; height: 100%; z-index: ${t.zIndex};` : `left: ${t.x}px; top: ${t.y}px; width: ${t.width}px; height: ${t.height}px; z-index: ${t.zIndex};` : "";
		},
		initPointerEvents() {
			this.pointerMoveHandler = (e) => {
				this.handlePointerMove(e);
			}, this.pointerUpHandler = (e) => {
				this.handlePointerUp(e);
			}, this.viewportResizeHandler = () => {
				this.handleWorkspaceResize();
			}, window.addEventListener("pointermove", this.pointerMoveHandler), window.addEventListener("pointerup", this.pointerUpHandler), window.addEventListener("resize", this.viewportResizeHandler);
		},
		startDrag(e, t) {
			if (e.button !== 0) return;
			let n = this.getWindow(t);
			!n || n.maximized || (this.focusWindow(t, { syncUrl: !0 }), this.dragState = {
				id: t,
				startPointerX: e.clientX,
				startPointerY: e.clientY,
				startX: n.x,
				startY: n.y
			}, document.body.style.userSelect = "none", document.body.style.cursor = "grabbing", e.preventDefault());
		},
		startResize(e, t, n) {
			if (e.button !== 0) return;
			let r = this.getWindow(t);
			!r || r.maximized || r.resizable === !1 || (this.focusWindow(t, { syncUrl: !0 }), this.resizeState = {
				id: t,
				direction: n,
				startPointerX: e.clientX,
				startPointerY: e.clientY,
				startX: r.x,
				startY: r.y,
				startWidth: r.width,
				startHeight: r.height
			}, document.body.style.userSelect = "none", e.preventDefault());
		},
		handlePointerMove(e) {
			if (this.dockDrag?.active) {
				this.handleDockDragMove(e);
				return;
			}
			if (this.dragState) {
				this.handleDrag(e);
				return;
			}
			this.resizeState && this.handleResize(e);
		},
		handleDrag(e) {
			let t = this.dragState, n = this.getWindow(t.id);
			if (!n) return;
			let r = this.getWorkspaceRect(), i = e.clientX - t.startPointerX, a = e.clientY - t.startPointerY, o = t.startX + i, s = t.startY + a;
			n.x = this.clamp(o, 0, Math.max(0, r.width - n.width)), n.y = this.clamp(s, 0, Math.max(0, r.height - 40));
		},
		handleResize(e) {
			let t = this.resizeState, n = this.getWindow(t.id);
			if (!n) return;
			let r = this.getWorkspaceRect(), i = e.clientX - t.startPointerX, a = e.clientY - t.startPointerY, o = t.direction, s = Math.min(n.minWidth, r.width), c = Math.min(n.minHeight, r.height);
			if (o.includes("e") && (n.width = this.clamp(t.startWidth + i, s, r.width - t.startX)), o.includes("s") && (n.height = this.clamp(t.startHeight + a, c, r.height - t.startY)), o.includes("w")) {
				let e = t.startX + t.startWidth, r = this.clamp(t.startX + i, 0, e - s);
				n.x = r, n.width = e - r;
			}
			if (o.includes("n")) {
				let e = t.startY + t.startHeight, r = this.clamp(t.startY + a, 0, e - c);
				n.y = r, n.height = e - r;
			}
		},
		handlePointerUp(e) {
			this.dockDrag && this.handleDockDragEnd(e);
			let t = !!(this.dragState || this.resizeState);
			this.dragState = null, this.resizeState = null, document.body.style.userSelect = "", document.body.style.cursor = "", t && this.scheduleWindowSessionSave();
		},
		handleWorkspaceResize() {
			let e = this.getWorkspaceRect();
			Object.values(this.windows).forEach((t) => {
				!t.open || t.maximized || (t.width = Math.min(t.width, e.width), t.height = Math.min(t.height, e.height), t.x = this.clamp(t.x, 0, Math.max(0, e.width - t.width)), t.y = this.clamp(t.y, 0, Math.max(0, e.height - 40)));
			}), this.scheduleWindowSessionSave();
		}
	};
}
//#endregion
//#region packages/novay/minios/resources/js/minios.js
function c(c = {}, l = {}, u = "/") {
	return o({
		applications: c,
		settings: l,
		basePath: u || "/",
		initialized: !1,
		init() {
			if (!this.initialized) {
				this.initialized = !0, this.bootstrapWindows(), this.restoreWindowSession();
				try {
					let e = localStorage.getItem("minios:dock:pinned_apps");
					if (e) {
						let t = JSON.parse(e);
						Array.isArray(t) && t.length > 0 && (this.settings ||= {}, this.settings.dock || (this.settings.dock = {}), (!this.settings.dock.pinned_apps || !Array.isArray(this.settings.dock.pinned_apps)) && (this.settings.dock.pinned_apps = t));
					}
				} catch {}
				this.initClock(), this.initSettingsListener(), this.initNotificationListener(), this.initAudioSystem(), this.initPointerEvents(), this.initKeyboardShortcuts(), this.initRouter(), this.pagehideHandler = () => this.flushWindowSession(), this.visibilityChangeHandler = () => {
					document.visibilityState === "hidden" && this.flushWindowSession();
				}, window.addEventListener("pagehide", this.pagehideHandler), document.addEventListener("visibilitychange", this.visibilityChangeHandler), this.globalContextMenuHandler = (e) => {
					e.preventDefault();
				}, document.addEventListener("contextmenu", this.globalContextMenuHandler), this.$nextTick(() => {
					this.initialized && this.handleWorkspaceResize();
				});
			}
		},
		destroy() {
			this.initialized && (this.flushWindowSession(), this.initialized = !1, window.removeEventListener("pagehide", this.pagehideHandler), document.removeEventListener("visibilitychange", this.visibilityChangeHandler), this.globalContextMenuHandler &&= (document.removeEventListener("contextmenu", this.globalContextMenuHandler), null), this.keydownHandler && window.removeEventListener("keydown", this.keydownHandler, !0), this.clockTimer && clearInterval(this.clockTimer), this.popstateHandler && window.removeEventListener("popstate", this.popstateHandler), this.pointerMoveHandler && window.removeEventListener("pointermove", this.pointerMoveHandler), this.pointerUpHandler && window.removeEventListener("pointerup", this.pointerUpHandler), this.viewportResizeHandler && window.removeEventListener("resize", this.viewportResizeHandler), this.persistTimer && clearTimeout(this.persistTimer), this.clockTimer = null, this.persistTimer = null);
		},
		clamp(e, t, n) {
			return n < t ? n : Math.min(Math.max(e, t), n);
		},
		getWorkspaceRect() {
			if (this.$refs?.workspace) {
				let e = this.$refs.workspace.getBoundingClientRect();
				if (e.width > 0 && e.height > 0) return {
					width: e.width,
					height: e.height
				};
			}
			return {
				width: Math.max(1, window.innerWidth - 64),
				height: Math.max(1, window.innerHeight - 28)
			};
		}
	}, a(), t(), i(), e(), r(), n(), s());
}
//#endregion
//#region packages/novay/minios/resources/js/bundle.js
typeof window < "u" && (window.minios = c, window.Alpine ? window.Alpine.data("minios", c) : document.addEventListener("alpine:init", () => {
	window.Alpine.data("minios", c);
}));
var l = c;
//#endregion
export { l as default };
