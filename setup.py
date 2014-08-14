"""Installer for memorised
"""

import os
cwd = os.path.dirname(__file__)

try:
    from setuptools import setup, find_packages
except ImportError:
    from ez_setup import use_setuptools
    use_setuptools()
    from setuptools import setup, find_packages


from memorised import compat


# python-memcached does not have a release compatible with Python 3,
# so use fork: https://github.com/eguven/python3-memcached
if compat.PY3:
    install_requires = ('python3-memcached',)
else:
    install_requires = ('python-memcached',)


setup(
        name='memorised',
        version='1.1.0',
        author='Wes Mason',
        author_email='wes@1stvamp.org',
        description='memcache memoization decorators and utils for python',
        long_description=open(os.path.join(cwd, 'README.rst')).read(),
        url='http://github.com/1stvamp/memorised/',
        packages=find_packages(exclude=('ez_setup',)),
        install_requires=install_requires,
        license='BSD',
        classifiers = [
                'Programming Language :: Python',
                'Programming Language :: Python :: 2',
                'Programming Language :: Python :: 2.6',
                'Programming Language :: Python :: 2.7',
                'Programming Language :: Python :: 3',
                'Programming Language :: Python :: 3.2',
                'Programming Language :: Python :: 3.3',
                'Programming Language :: Python :: 3.4',
        ],
)
