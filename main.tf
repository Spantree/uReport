provider "heroku" {
  version = "~> 1.7"
}

resource "heroku_app" "richmond_311" {
  name = "richmond-311"
  region = "us"
  buildpacks = [
    "heroku/php"
  ]
}

resource "heroku_build" "richmond_311" {
  app = "${heroku_app.richmond_311.name}"

  source = {
    path = "./crm"
    version = "2.0.2"
  }
}

resource "heroku_addon" "richmond_311_db" {
  app = "${heroku_app.richmond_311.name}"
  plan = "jawsdb-maria:kitefin"
}

output "app_url" {
  value = "https://${heroku_app.richmond_311.name}.herokuapp.com"
}
