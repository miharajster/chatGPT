<script>
import { createApp } from 'vue';
import axios from 'axios';
import VueAxios from 'vue-axios';
import Stage1 from './components/Stage1.vue';
import Stage2 from './components/Stage2.vue';
import Stage3 from './components/Stage3.vue';
import Error from './components/Error.vue';

const app = createApp({});
app.use(VueAxios, axios); // Use VueAxios and axios
export default {
  components: {
    Stage1,
    Stage2,
    Stage3,
    Error
  },
  data() {
    return {
      stage: 1,
      countdown: 1800, // 30 minutes in seconds
      course: {
        name: "Your course name",
        keywords: "Your keywords",
        abstract: "Your abstract",
        content: "Your content",
        whats_included: "What's included",
        included: "Included",
        prerequisites: "Prerequisites",
        syllabus: "Syllabus",
        readings: "Readings",
        faq: "Frequently Asked Questions",
        task: "Your task"
      }
    };
  },
  methods: {
    handleWrite(inputContent) {
      this.stage = 2; // Move to Stage 2
      this.startCountdown();

      // Trigger API POST call using Axios to the backend
      axios.post(
          'http://localhost/chatGPT/api.php',
          { prompt: inputContent },
          { timeout: 1800000 })
          .then(response => {
            // Mappings
            this.course.name = response.data.course_name;

            this.course.keywords = '';
            for(let i = 0; i < response.data.keywords.length; i++){
              this.course.keywords += response.data.keywords[i] + ', '
            }

            this.course.abstract = response.data.abstract;

            this.course.content = '';
            for(let i = 0; i < response.data.content.length; i++) {
              this.course.content += '<h2>' + response.data.content[i].title + "</h2>";
              this.course.content += '<p>' + response.data.content[i].content + "</p>";
              this.course.content += '<h3>Quiz</h3>';
              for(let o = 0; o < response.data.content[i].quiz.length; o++) {
                this.course.content += '<p><strong>Q: ' + response.data.content[i].quiz[o].question + '</strong></p>';
                this.course.content += '<p>A: ' + response.data.content[i].quiz[o].answer + '</p>';
                this.course.content += '<p>L: ' + response.data.content[i].quiz[o].learned + '</p>';
              }
              this.course.content += '<h3>Keywords</h3>';
              this.course.content += '<p>' + response.data.content[i].keywords + "</p>";
              this.course.content += '<h3>Suggested lession type: ' + response.data.content[i].suggested_lesson_type + "</h3>";
            }

            this.course.whats_included = response.data.whats_included;

            this.course.included = '<ul>';
            for(let i = 0; i < response.data.content.length; i++) {
              this.course.included += '<li>' + response.data.included[i].title + '</li>';
            }
            this.course.included += '</ul>';

            this.course.prerequisites = response.data.prerequisites;
            this.course.syllabus = response.data.syllabus;

            this.course.readings = '';
            for(let i = 0; i < response.data.readings.length; i++) {
              this.course.readings += '<p><string>' + response.data.readings[i].title + '</string>' + response.data.readings[i].author + '</p>'
            }

            this.course.faq = '';
            for(let i = 0; i < response.data.faq.length; i++) {
              this.course.content += '<p><strong>Q: ' + response.data.faq[i].q + '</strong></p>';
              this.course.content += '<p>A: ' + response.data.faq[i].a + '</p>';
            }

            this.course.task = '<h2>' + response.data.task.title + '</h2>';
            this.course.task = '<p>' + response.data.task.instructions + '</p>';
            this.course.task = '<p>' + response.data.task.learned + '</p>';
            // End of Mappings

            this.stage = 3; // Move to Stage 2
          })
          .catch(error => {
            this.stage = 0; // Move to Error
            console.log(error);
          });
    },
    startCountdown() {
      // Start Countdown
      const countdownInterval = setInterval(() => {
        this.countdown--;
        if (this.countdown <= 0) {
          clearInterval(countdownInterval);
          // Move to Error when countdown reaches 0
          this.stage = 0;
        }
      }, 1000);
    },
    updateCourse(course) {
      // Mainly so we can pass course and not every string by itself
      this.course = course;
    }
  },
};
</script>

<template>
  <div>
    <Stage1 v-if="stage === 1" @write="handleWrite"/>
    <Stage2 v-if="stage === 2" :countdown="countdown"/>
    <Stage3 v-if="stage === 3" :course="course" @update:course="updateCourse"/>
    <Error  v-if="stage === 0"/>
  </div>
</template>
