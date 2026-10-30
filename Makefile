SHELL := /bin/bash
.DEFAULT_GOAL := help

# Machine-specific paths and overrides (gitignored).
-include local.mk

FLUTTER      ?= flutter
ANDROID_HOME ?= $(HOME)/Android/Sdk
JAVA_HOME    ?=
AVD          ?= Pixel_5_API33_Linux
DEVICE       ?= emulator-5554
EMU_FLAGS    ?= -gpu swiftshader_indirect -no-snapshot -no-boot-anim
LOG_DIR      ?= $(CURDIR)/build/logs
SHOT_DIR     ?= $(CURDIR)/build/screenshots

EMULATOR ?= $(ANDROID_HOME)/emulator/emulator
ADB      ?= $(ANDROID_HOME)/platform-tools/adb
EMU_LOG  := $(LOG_DIR)/emulator.log

ANDROID_USER_HOME ?= $(HOME)/.android
ANDROID_AVD_HOME  ?= $(ANDROID_USER_HOME)/avd
GRADLE_USER_HOME  ?= $(HOME)/.gradle
PUB_CACHE         ?= $(HOME)/.pub-cache

export ANDROID_HOME ANDROID_USER_HOME ANDROID_AVD_HOME GRADLE_USER_HOME PUB_CACHE
export PATH := $(ANDROID_HOME)/cmdline-tools/latest/bin:$(ANDROID_HOME)/platform-tools:$(ANDROID_HOME)/emulator:$(dir $(FLUTTER)):$(PATH)
ifneq ($(JAVA_HOME),)
export JAVA_HOME
export PATH := $(JAVA_HOME)/bin:$(PATH)
endif

.PHONY: help check-env emulator run app build analyze test doctor devices logs screenshot stop

help:
	@echo "make run        Boot the emulator if needed, then run the app (hot reload)"
	@echo "make app        Boot the emulator, install the prebuilt APK, and launch it"
	@echo "make emulator   Boot the emulator only"
	@echo "make stop       Shut down the emulator"
	@echo "make build      Build the debug APK"
	@echo "make analyze    Run flutter analyze"
	@echo "make test       Run flutter test"
	@echo "make doctor     Run flutter doctor -v"
	@echo "make devices    List adb and Flutter devices"
	@echo "make logs       Stream Flutter logcat"
	@echo "make screenshot Save an emulator screenshot to $(SHOT_DIR)"

check-env:
	@test -x "$(FLUTTER)"  || { echo "Missing flutter: $(FLUTTER) (set FLUTTER in local.mk)"; exit 1; }
	@test -x "$(EMULATOR)" || { echo "Missing emulator: $(EMULATOR) (set ANDROID_HOME in local.mk)"; exit 1; }
	@test -x "$(ADB)"      || { echo "Missing adb: $(ADB) (set ANDROID_HOME in local.mk)"; exit 1; }
	@test -r /dev/kvm      || { echo "KVM unavailable. Enable it before starting the emulator."; exit 1; }

emulator: check-env
	@mkdir -p "$(LOG_DIR)"
	@if ! "$(ADB)" devices | grep -q "$(DEVICE)"; then \
		echo "Starting $(AVD)..."; \
		nohup "$(EMULATOR)" -avd "$(AVD)" $(EMU_FLAGS) >"$(EMU_LOG)" 2>&1 & \
	fi
	@timeout 300 "$(ADB)" wait-for-device || { echo "Emulator did not appear; see $(EMU_LOG)"; exit 1; }
	@printf "Waiting for Android to boot"
	@timeout 300 bash -c 'until "$(ADB)" shell getprop sys.boot_completed 2>/dev/null | tr -d "\r" | grep -qx 1; do printf "."; sleep 2; done' \
		|| { echo " Boot timed out; see $(EMU_LOG)"; exit 1; }
	@echo " ready."

run: emulator
	"$(FLUTTER)" run -d "$(DEVICE)"

app: emulator
	@test -f build/app/outputs/flutter-apk/app-debug.apk || $(MAKE) build
	"$(ADB)" install -r build/app/outputs/flutter-apk/app-debug.apk
	"$(ADB)" shell am start -n com.example.flutter_project/.MainActivity

build: check-env
	"$(FLUTTER)" build apk --debug

analyze:
	"$(FLUTTER)" analyze --no-fatal-infos

test:
	"$(FLUTTER)" test

doctor:
	"$(FLUTTER)" doctor -v

devices: check-env
	@"$(ADB)" devices -l
	@"$(FLUTTER)" devices --show-web-server-device

logs: check-env
	@"$(ADB)" logcat -s flutter

screenshot: check-env
	@mkdir -p "$(SHOT_DIR)"
	@"$(ADB)" exec-out screencap -p > "$(SHOT_DIR)/screen-$$(date +%Y%m%d-%H%M%S).png"
	@echo "Saved to $(SHOT_DIR)"

stop:
	-@"$(ADB)" -s "$(DEVICE)" emu kill
