import ramjet from 'ramjet'


class CoverTransition{
  constructor(){
    this.fromElement = null
    this.toElement = null
  }

  setFromElement(el){
    this.fromElement = el
  }

  setToElement(el){
    this.toElement = el
    this.startIfReady()
  }

  startIfReady(){
    if(this.fromElement && this.toElement){
      this.start()
    }
  }

  async start(){
    console.log("Start Animation")
    var bgImage = ""
    this.toElement.style.opacity = 0
    this.fromElement.style.opacity = 0
    var toElement = this.toElement
    await ramjet.transform(this.fromElement, this.toElement, {
      duration: 300,
      overrideClone(node, depth){
        var clone = node.cloneNode()
        if(clone.getAttribute("data-image-url")){
          var bgImage = clone.getAttribute("data-image-url");
          var img = document.createElement("img")
          img.setAttribute("src", bgImage)
          img.style.width = "100%"
          img.style.height = "100%"
          clone.appendChild(img)
        }
        return clone
      },
      easing(t){
          return 1 - Math.pow(1 - t, 3);
        },
      done: () => {
        toElement.style.opacity = 1
      }
    })
    setTimeout(() => {
      toElement.style.opacity = 1
      this.fromElement = null
      this.toElement = null
    }, 290)
  }
}


var coverTransition = new CoverTransition()

export default coverTransition
