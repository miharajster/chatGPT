<template>
  <div id="stage3">
    <h1>Course content</h1>
    <label for="courseName">Course Name:</label>
    <input id="courseName" :value="course.name" @input="updateCourse('name', $event.target.value)">

    <label for="keywords">Keywords:</label>
    <textarea id="keywords" :value="course.keywords" @input="updateCourse('keywords', $event.target.value)"></textarea>

    <label for="abstract">Abstract:</label>
    <textarea id="abstract" :value="course.abstract" @input="updateCourse('abstract', $event.target.value)"></textarea>

    <label for="content">Content:</label>
    <textarea id="content" :value="course.content" @input="updateCourse('content', $event.target.value)"></textarea>

    <label for="whats_included">What is included:</label>
    <textarea id="whats_included" :value="course.whats_included" @input="updateCourse('whats_included', $event.target.value)"></textarea>

    <label for="included">Included:</label>
    <textarea id="included" :value="course.included" @input="updateCourse('included', $event.target.value)"></textarea>

    <label for="prerequisites">Prerequisites:</label>
    <textarea id="prerequisites" :value="course.prerequisites" @input="updateCourse('prerequisites', $event.target.value)"></textarea>

    <label for="syllabus">Syllabus:</label>
    <textarea id="syllabus" :value="course.syllabus" @input="updateCourse('syllabus', $event.target.value)"></textarea>

    <label for="readings">Readings:</label>
    <textarea id="readings" :value="course.readings" @input="updateCourse('readings', $event.target.value)"></textarea>

    <label for="faq">FAQ:</label>
    <textarea id="faq" :value="course.faq" @input="updateCourse('faq', $event.target.value)"></textarea>

    <label for="task">Task:</label>
    <textarea id="task" :value="course.task" @input="updateCourse('task', $event.target.value)"></textarea>

  </div>
</template>

<script>
export default {
  props: ['course'],
  methods: {
    updateCourse(field, value) {
      this.$emit('update:course', { ...this.course, [field]: value });
    }
  }
};
</script>
