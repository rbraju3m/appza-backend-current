#!/bin/bash
set -euo pipefail  # Enable strict error handling

# Debugging: Save logs to /tmp for troubleshooting
DEBUG_LOG_DIR="storage/logs"

DEBUG_LOG="$DEBUG_LOG_DIR/laravel.log"

# Create the log directory if it doesn't exist
mkdir -p "$DEBUG_LOG_DIR"
chmod -R 775 "$DEBUG_LOG_DIR"

# Clear previous logs
echo "" > "$DEBUG_LOG"

# Log messages with timestamps
log() {
    local level=$1
    local message=$2
    echo -e "$(date '+%Y-%m-%d %H:%M:%S') [$level] $message" | tee -a "$DEBUG_LOG"
}

# Print all received arguments (Debugging)
log "INFO" "📌 DEBUG: Received Arguments -> $@"

### 📌 Define Default Constants
readonly KEYSTORE_FILE_NAME="upload-keystore.jks"
readonly KEY_PROPERTIES_FILE_NAME="key.properties"

### 🛠️ Function to Display Usage Information
usage() {
    cat << EOF
Usage: $(basename "$0") --store-pass PASSWORD --key-pass PASSWORD --replace y/n --folder FOLDER_NAME [OPTIONS]

Required Arguments:
    --store-pass PASSWORD     Keystore password (min 6 chars)
    --key-pass PASSWORD       Key password (min 6 chars)
    --replace y/n             Replace existing keystore? (y/n)
    --folder FOLDER_NAME      Folder where the keystore should be stored

Optional Arguments:
    --cn TEXT                Common Name
    --ou TEXT                Organizational Unit
    --org TEXT               Organization
    --location TEXT          City/Location
    --state TEXT             State/Province
    --country TEXT           Country Code
    --help                   Show this help message

Example:
    $(basename "$0") --store-pass 123456 --key-pass 123456 --replace y --folder myapp \
                     --cn "My App" --ou "My Unit" --org "My Company" \
                     --location "San Francisco" --state "CA" --country "US"
EOF
    exit 1
}

### 📜 Parse Command Line Arguments
parse_params() {
    STOREPASS=""
    KEYPASS=""
    REPLACE=""
    FOLDER=""
    CN=""
    OU=""
    O=""
    L=""
    ST=""
    C=""

    log "INFO" "🔍 DEBUG: Parsing Arguments..."

    while [[ $# -gt 0 ]]; do
        log "INFO" "Processing argument: $1"
        case "$1" in
            --store-pass) STOREPASS="$2"; log "INFO" "Parsed --store-pass: $STOREPASS"; shift 2;;
            --key-pass) KEYPASS="$2"; log "INFO" "Parsed --key-pass: $KEYPASS"; shift 2;;
            --replace) REPLACE="$2"; log "INFO" "Parsed --replace: $REPLACE"; shift 2;;
            --folder) FOLDER="$2"; log "INFO" "Parsed --folder: $FOLDER"; shift 2;;   # ✅ Handling --folder argument properly
            --cn) CN="$2"; log "INFO" "Parsed --cn: $CN"; shift 2;;
            --ou) OU="$2"; log "INFO" "Parsed --ou: $OU"; shift 2;;
            --org) O="$2"; log "INFO" "Parsed --org: $O"; shift 2;;
            --location) L="$2"; log "INFO" "Parsed --location: $L"; shift 2;;
            --state) ST="$2"; log "INFO" "Parsed --state: $ST"; shift 2;;
            --country) C="$2"; log "INFO" "Parsed --country: $C"; shift 2;;
            --help) usage;;
            *) log "ERROR" "Unknown argument: $1"; usage;;
        esac
    done

    # Ensure required parameters are set
    if [[ -z "$STOREPASS" || -z "$KEYPASS" || -z "$REPLACE" || -z "$FOLDER" ]]; then
        log "ERROR" "Required parameters missing!"
        log "ERROR" "STOREPASS: $STOREPASS, KEYPASS: $KEYPASS, REPLACE: $REPLACE, FOLDER: $FOLDER"
        usage
    fi

    log "INFO" "✅ DEBUG: Final Parsed Values: STOREPASS=[hidden] KEYPASS=[hidden] REPLACE=$REPLACE FOLDER=$FOLDER"
}

### 📂 Set Up the Target Folder
setup_directory() {
    BASE_STORAGE_DIR="$(dirname "$(dirname "$(realpath "$0")")")/jks"
    TARGET_DIR="$BASE_STORAGE_DIR/$FOLDER"

    if [[ ! -d "$BASE_STORAGE_DIR" ]]; then
        log "ERROR" "Base storage directory does not exist: $BASE_STORAGE_DIR"
        exit 1
    fi

    mkdir -p "$TARGET_DIR"
    if [[ $? -ne 0 ]]; then
        log "ERROR" "Failed to create target directory: $TARGET_DIR"
        exit 1
    fi

    chmod -R 775 "$TARGET_DIR"
    if [[ $? -ne 0 ]]; then
        log "ERROR" "Failed to set permissions for target directory: $TARGET_DIR"
        exit 1
    fi

    readonly KEYSTORE_FILE="$TARGET_DIR/$KEYSTORE_FILE_NAME"
    readonly KEY_PROPERTIES_FILE="$TARGET_DIR/$KEY_PROPERTIES_FILE_NAME"

    log "INFO" "✅ Using storage directory: $TARGET_DIR"
}

### 🛑 Cleanup Function in Case of Errors
cleanup() {
    log "ERROR" "Script failed. Removing $KEYSTORE_FILE and $KEY_PROPERTIES_FILE"
    rm -f "$KEYSTORE_FILE" "$KEY_PROPERTIES_FILE"
    exit 1
}
trap cleanup ERR  # Run cleanup if script fails

### 🚀 MAIN FUNCTION TO EXECUTE THE SCRIPT
main() {
    parse_params "$@"  # Parse command-line arguments
    setup_directory     # Set up the storage folder

    ### 🔍 Handle Existing Keystore
    if [[ -f "$KEYSTORE_FILE" ]]; then
        case "$(echo "$REPLACE" | tr '[:upper:]' '[:lower:]')" in
            y|yes) log "INFO" "Replacing existing keystore."; rm -f "$KEYSTORE_FILE";;
            n|no) log "INFO" "Exiting without replacing."; exit 0;;
            *) log "ERROR" "Invalid --replace value. Use 'y' or 'n'."; exit 1;;
        esac
    fi

    ### 🔐 Generate Keystore using `keytool`
    log "INFO" "🚀 Generating Keystore..."

    if ! keytool -genkey -v -keystore "$KEYSTORE_FILE" -keyalg RSA -keysize 2048 \
            -validity 10000 -alias app -dname "CN=$CN, OU=$OU, O=$O, L=$L, ST=$ST, C=$C" \
            -storepass "$STOREPASS" -keypass "$KEYPASS"; then
        log "ERROR" "Failed to generate keystore."
        exit 1
    fi

    log "INFO" "✅ Keystore successfully generated at: $KEYSTORE_FILE"

    ### 📜 Create Key Properties File
    log "INFO" "Creating key.properties file..."
    cat <<EOL > "$KEY_PROPERTIES_FILE"
storePassword=$STOREPASS
keyPassword=$KEYPASS
keyAlias=app
storeFile=../app/upload-keystore.jks
EOL

    if [[ -f "$KEY_PROPERTIES_FILE" ]]; then
        log "INFO" "✅ Key properties file created successfully!"
    else
        log "ERROR" "Failed to create key.properties file."
        exit 1
    fi
}

### 🎯 Execute Main Function
main "$@"
