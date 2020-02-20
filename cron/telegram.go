package main
import (
    "fmt"
    "database/sql"
    _ "github.com/go-sql-driver/mysql"
    "github.com/jmoiron/sqlx"
    "time"
    "encoding/json"
    "net/http"
    "os"
    "syscall"
    "io/ioutil"
    "strconv"
    "github.com/joho/godotenv"
    "log"
    "path"
    "path/filepath"

    //"errors"
)

type Queue struct {
	ID   int    `db:"id"`
	Type string `db:"type"`
	Data string `db:"data"`
	DateSend sql.NullString `db:"date_send"`
	Error string `db:"error"`
	IdWorker sql.NullString `db:"id_worker"`
	Cdate string `db:"cdate"`
}

type UserData struct {
    UserID int `json:"userID"`
    Date string `json:"date"`
    Msg string `json:"msg"`
}

const (
    TYPE_TELEGRAM = "telegram"
)

var mainPath string

func init() {

    thepath := "../.env"

    if len(os.Args) > 1 {

        filename := os.Args[1] // get command line first parameter

        filedirectory := filepath.Dir(filename)

        abspath, err := filepath.Abs(filedirectory)

        if err != nil {
            log.Fatal(err)
        }
        thepath = path.Join(abspath, "/.env")
        mainPath = abspath;
    }

    if err := godotenv.Load(thepath); err != nil {
        log.Print("No .env file found")
    }
}

func main() {
    if isRunning() {
        return
    }

    for {
         run()
         sleep()
    }
}

func sleep() {
    time.Sleep(5 * time.Second)
}

func run() {
    dsn := fmt.Sprintf(
        "%s:%s@tcp(%s:%s)/%s",
        os.Getenv("DB_USER"),
        os.Getenv("DB_PASSWORD"),
        os.Getenv("DB_HOST"),
        os.Getenv("DB_PORT"),
        os.Getenv("DB_NAME"))

    conn, err := sqlx.Connect("mysql", dsn)

    if err != nil {
        panic(err)
    }

    var queues []Queue

    loc, _ := time.LoadLocation("UTC")
    currentTime := time.Now().In(loc).Add(2 * time.Hour)

    currentDate := currentTime.Format("2006-01-02")

    err = conn.Select(&queues, "select * from queue where type = ? and date_send <= ?", TYPE_TELEGRAM, currentDate)
    if err != nil {
        panic(err)
    }

    for _, value := range queues {
        userInfo := UserData{}
        json.Unmarshal([]byte(value.Data), &userInfo)

        layout := "2006-01-02 15:04:05"
        str := userInfo.Date
        userTime, err := time.Parse(layout, str)
        if err != nil {
            log.Print(err)
        }

        if userTime.Before(currentTime) {

            err = sendMessage(userInfo)
            if (err != nil) {
                panic(err)
            }
            _, err = conn.Exec("DELETE FROM queue where id=?", value.ID)
            if err != nil {
                panic(err)
            }
        }
    }
}

func sendMessage(userInfo UserData) (error) {
    var url string
    url = fmt.Sprintf(
        "https://api.telegram.org/bot%s/sendMessage?chat_id=%d&text=%s",
        os.Getenv("TELEGRAM_TOKEN"),
        userInfo.UserID,
        userInfo.Msg);

    _, err := http.Get(url)
    if err != nil {
        return err
    }

    return nil
}

func isRunning() (bool) {
     lockFilePath := getLockFile();
     data, err := ioutil.ReadFile(lockFilePath)
     if err != nil {
         panic(err)
     }

     lastPidString := string(data)
     if len(lastPidString) > 0 {
        lastPid, _ := strconv.Atoi(lastPidString)
        process, _ := os.FindProcess(lastPid)

        err = process.Signal(syscall.Signal(0))
        if err == nil {
            // "Process Exist"
            return true;
        }
     }

     pid := os.Getpid()

     message := []byte(string(strconv.Itoa(pid)))
     err = ioutil.WriteFile(lockFilePath, message, 0644)
     if err != nil {
        panic(err)
     }

     return false;
}

func getLockFile() (string) {
    var pathLock string = "lock/telegram_lock.lck"

    if len(mainPath) > 0 {
        return path.Join(mainPath, "/cron/", pathLock);
    }
    return pathLock
}